<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\Product;
use App\Models\studentRunningNumber;
use App\Models\term;
use App\Models\Sterm;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\OrderDetail;
use App\Models\OrderRunningNumber;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ContactController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:student-list|student-create|student-edit|student-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:student-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:student-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:student-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            //sleep(2);
            //dd($request->get('approved'));
            $query = Contact::select(
                "contacts.*",
                "departments.name as centre_name",
                "start_terms.name as start_term_name",
            )
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("levels", "contacts.level", "=", "levels.id")
                ->join("bookuses", "contacts.bookuse", "=", "bookuses.id")
                ->join("terms as start_terms", "contacts.start_term", "=", "start_terms.id")
                ->join("terms as pterms", "contacts.term", "=", "pterms.id")
                ->where('discontinued', '0');

            if (!Gate::allows('all-centre')) {
                $query->where('centre', Auth::user()->department->id);
            }

            if (!empty($request->get('sdate'))) {
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('contacts.start_date', [$startDate, $endDate]);
                    }
                }
            }

            $query->orderBy("contacts.id", "desc");
            $datas = $query->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('level_name', function ($row) {
                    if (!empty($row->level2_name)) {
                        return $row->level_name . ' - ' . $row->level2_name;
                    } else {
                        return $row->level_name;
                    }
                })
                ->editColumn('bookuse_name', function ($row) {
                    if (!empty($row->bookuse2_name)) {
                        return ' ( ' . $row->bookuse_name . ') - (' . $row->bookuse2_name . ')';
                    } else {
                        return $row->bookuse_name;
                    }
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('student-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    if (Gate::allows('student-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action', 'more'])->toJson();
            /* ->filter(function ($instance) use ($request) {
                    if ($request->get('approved') == '0' || $request->get('approved') == '1') {
                        $instance->where('contacts.code', $request->get('approved'));
                    }
                    if (!empty($request->get('search'))) {
                        $instance->where(function ($w) use ($request) {
                            $search = $request->get('search');
                            $w->orWhere('name', 'LIKE', "%$search%")
                                ->orWhere('email', 'LIKE', "%$search%");
                        });
                    }
                }) */
        }

        $centre = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $term = term::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $sterm = Sterm::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $bookuse = bookuse::where([['status', '1']/* , ['type', '1'] */])
            ->orderBy("name", "asc")->get();
        $level = level::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        //dd($term);

        return view('contacts.index')->with(['centre' => $centre])
            ->with(['bookuse' => $bookuse])
            ->with(['level' => $level])
            ->with(['term' => $term])
            ->with(['sterm' => $sterm]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($centre)
    {
        if ($centre == 0) {
            $centre = Auth::user()->department->id;
        }
        $rnumber = studentRunningNumber::pre_generate($centre);
        //dd($rnumber);
        return response()->json([
            'running' =>  $rnumber
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:contacts',
            'start_date' => 'required|string|max:10',
            'start_term' => 'required',
            'school' => 'required|string|max:255',
            'level' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'father_name' => 'nullable|string|max:255|required_without:mother_name',
            'father_email' => 'nullable|string|max:255|required_with:father_name,father_mobile',
            'father_mobile' => 'nullable|string|max:20|required_with:father_name,father_email',
            'mother_name' => 'nullable|string|max:255|required_without:father_name',
            'mother_email' => 'nullable|string|max:255|required_with:mother_name,mother_mobile',
            'mother_mobile' => 'nullable|string|max:20|required_with:mother_name,mother_email',
        ], [
            'name.required' => 'Student name must not be empty.',
            'name.unique' => 'This student name already exists in the database.',
            'start_date.required' => 'Please provide a start date.',
            'start_term.required' => 'Please select a start term.',
            'school.required' => 'Please provide the school name.',
            'level.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
            'father_name.required_without' => 'Either Father name or Mother name must be provided.',
            'father_email.required_with' => 'Father email is required when Father name is provided.',
            'father_mobile.required_with' => 'Father mobile is required when Father name is provided.',
            'mother_name.required_without' => 'Either Mother name or Father name must be provided.',
            'mother_email.required_with' => 'Mother email is required when Mother name is provided.',
            'mother_mobile.required_with' => 'Mother mobile is required when Mother name is provided.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $bookq = bookuse::find($request->get('bookuse'));
        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));

        if ($bookq->type == 1) {
            $mprice = $levelq->price;
            $fprice = $levelq->price;
        } elseif ($bookq->type == 2) {
            $mprice = $levelq->half_price;
            $fprice = $levelq->half_price;
        }

        if (!empty($request->get('level2'))) {

            $bookq2 = bookuse::find($request->get('bookuse2'));
            $levelq2 = Level::find($request->get('level2'));
            $termq2 = Term::find($request->get('term2'));

            $mprice = 0;
            $mbprice = 0;
            for ($i = $levelq->id; $i <= $levelq2->id; $i++) {
                $sbook = 1;

                if ($i == $levelq->id) {
                    $sterm = $termq->name;
                } else {
                    $sterm = 1;
                }

                if ($i == $levelq2->id) {
                    $eterm = $termq2->name;
                } else {
                    $eterm = 4;
                }

                for ($j = $sterm; $j <= $eterm; $j++) {
                    if ($i == $levelq->id && $j == $sterm) {
                        $sbook = $bookq->type;
                    } else {
                        $sbook = 1;
                    }
                    //echo $j . "<br>";
                    $books = bookuse::where('type', '=', $sbook)
                        ->where('level_id', '=', $i)
                        ->where('term_id', '=', $j)
                        ->get();

                    $lprice = level::where('id', $books[0]->level_id)->get();

                    $mprice = $mprice + $lprice[0]->price;
                    $mbprice = $mbprice + $lprice[0]->book_price;
                }
            }


            if ($bookq2->type == 1) {
                $fprice2 = $levelq2->price;
            } elseif ($bookq2->type == 2) {
                $fprice2 = $levelq2->half_price;
            }
        }



        $input = $request->all();
        $input['code'] = studentRunningNumber::generate($request->get('centre'));
        $input['level_name'] = $levelq->name;
        $input['term_name'] = $termq->name;
        $input['bookuse_name'] = $bookq->name;

        if (!empty($request->get('level2'))) {
            $input['level2_name'] = $levelq2->name;
            $input['term2_name'] = $termq2->name;
            $input['bookuse2_name'] = $bookq2->name;
        }

        $contact = Contact::create($input);

        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $contact->id;
        $input_order['order_number'] = OrderRunningNumber::generate($request->get('centre'));
        $input_order['total_price'] = $mprice;
        $order = Order::create($input_order);

        $contact->order = $order->id;
        $contact->save();

        $input_order_detail['order_id'] = $order->id;
        $input_order_detail['product_id'] = $bookq->id;
        $input_order_detail['pname'] = $bookq->name;
        $input_order_detail['punit'] = $bookq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $fprice;
        $order_detail = OrderDetail::create($input_order_detail);

        if (!empty($request->get('level2'))) {
            $input_order_detail2['order_id'] = $order->id;
            $input_order_detail2['product_id'] = $bookq2->id;
            $input_order_detail2['pname'] = $bookq2->name;
            $input_order_detail2['punit'] = $bookq2->unit;
            $input_order_detail2['quantity'] = 1;
            $input_order_detail2['price'] = $fprice2;
            $order_detail2 = OrderDetail::create($input_order_detail2);
        }

        $select_list_contact = '<option value="' . $contact->id . '" > ' . $contact->name . '</option>';
        return response()->json([
            'success' => 'Added student information successfully.',
            'contact' => $select_list_contact, 'cid' => $contact->id, 'oid' => $order->id
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //$data = Contact::find($id);
        $data = Order::select(
            "contacts.*",
            "departments.name as centre_name",
        )
            ->join("contacts", "contacts.id", "=", "orders.cid")
            ->join("departments", "contacts.centre", "=", "departments.id")
            ->where('cid', $id)
            ->orderBy("id", "desc")->first();

        $datas = OrderDetail::select(
            "order_details.*",
            "levels.id as level_id",
            "terms.id as term_id",
        )
            ->join("bookuses", "bookuses.id", "=", "order_details.product_id")
            ->join("terms", "terms.id", "=", "bookuses.term_id")
            ->join("levels", "levels.id", "=", "bookuses.level_id")
            ->where('order_id', $data->id)->orderBy("id", "asc")->get();

        return response()->json(['data' => $data, 'datas' => $datas]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:contacts,name,' . $id,
            'start_date' => 'required|string|max:10',
            'start_term' => 'required',
            'school' => 'required|string|max:255',
            'level' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            //'postcode' => 'integer|max:10',

        ];


        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Student name must not be empty.',
            'name.unique' => 'This student name already exists in the database.',
            'start_date.required' => 'Please provide a start date.',
            'start_term.required' => 'Please select a start term.',
            'school.required' => 'Please provide the school name.',
            'level.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $bookq = bookuse::find($request->get('bookuse'));
        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));

        if ($bookq->type == 1) {
            $mprice = $levelq->price;
            $fprice = $levelq->price;
        } elseif ($bookq->type == 2) {
            $mprice = $levelq->half_price;
            $fprice = $levelq->half_price;
        }

        if (!empty($request->get('level2'))) {

            $bookq2 = bookuse::find($request->get('bookuse2'));
            $levelq2 = Level::find($request->get('level2'));
            $termq2 = Term::find($request->get('term2'));

            $mprice = 0;
            $mbprice = 0;
            for ($i = $levelq->id; $i <= $levelq2->id; $i++) {
                $sbook = 1;

                if ($i == $levelq->id) {
                    $sterm = $termq->name;
                } else {
                    $sterm = 1;
                }

                if ($i == $levelq2->id) {
                    $eterm = $termq2->name;
                } else {
                    $eterm = 4;
                }

                for ($j = $sterm; $j <= $eterm; $j++) {
                    if ($i == $levelq->id && $j == $sterm) {
                        $sbook = $bookq->type;
                    } else {
                        $sbook = 1;
                    }
                    //echo $j . "<br>";
                    $books = bookuse::where('type', '=', $sbook)
                        ->where('level_id', '=', $i)
                        ->where('term_id', '=', $j)
                        ->get();

                    $lprice = level::where('id', $books[0]->level_id)->get();

                    $mprice = $mprice + $lprice[0]->price;
                    $mbprice = $mbprice + $lprice[0]->book_price;
                }
            }


            if ($bookq2->type == 1) {
                $fprice2 = $levelq2->price;
            } elseif ($bookq2->type == 2) {
                $fprice2 = $levelq2->half_price;
            }
        }



        $contactd = [
            'name' => $request->get('name'),
            'address' => $request->get('address'),
            'postcode' => $request->get('postcode'),
            'telephone' => $request->get('telephone'),
            'centre' => $request->get('centre'),
            'code' => $request->get('code'),
            'start_date' => $request->get('start_date'),
            'start_term' => $request->get('start_term'),
            'school' => $request->get('school'),
            'birth_date' => $request->get('birth_date'),
            'gender' => $request->get('gender'),
            'father_name' => $request->get('father_name'),
            'father_email' => $request->get('father_email'),
            'father_mobile' => $request->get('father_mobile'),
            'mother_name' => $request->get('mother_name'),
            'mother_email' => $request->get('mother_email'),
            'mother_mobile' => $request->get('mother_mobile'),
            'level' => $request->get('level'),
            'term' => $request->get('term'),
            'bookuse' => $request->get('bookuse'),
            'level_name' => $levelq->name,
            'term_name' => $termq->name,
            'bookuse_name' => $bookq->name,
            'level2' => $request->get('level2'),
            'term2' => $request->get('term2'),
            'bookuse2' => $request->get('bookuse2'),
            'level2_name' => !empty($levelq2->name) ? $levelq2->name : null,
            'term2_name' => !empty($termq2->name) ? $termq2->name : null,
            'bookuse2_name' => !empty($bookq2->name) ? $bookq2->name : null,
            'discontinued' => $request->get('discontinued'),
            'discontinued_date' => $request->get('discontinued_date'),
            'discontinued_reason' => $request->get('discontinued_reason'),
        ];

        $contact = Contact::find($id);
        $contact->update($contactd);

        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $contact->id;
        $input_order['total_price'] = $mprice;
        $order = Order::find($contact->order);

        if ($order) {
            $order->update($input_order);
        }

        $input_order_detail['order_id'] = $order->id ?? null; // Set to null if $order is not found
        $input_order_detail['product_id'] = $bookq->id;
        $input_order_detail['pname'] = $bookq->name;
        $input_order_detail['punit'] = $bookq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $fprice;
        $order_detail = OrderDetail::where('order_id', $input_order_detail['order_id'])->first();

        if ($order_detail) {
            $order_detail->update($input_order_detail);
        }

        if (!empty($request->get('level2'))) {
            $input_order_detail2['order_id'] = $order->id ?? null;
            $input_order_detail2['product_id'] = $bookq2->id;
            $input_order_detail2['pname'] = $bookq2->name;
            $input_order_detail2['punit'] = $bookq2->unit;
            $input_order_detail2['quantity'] = 1;
            $input_order_detail2['price'] = $fprice2;
            $order_detail2 = OrderDetail::where('order_id', $input_order_detail['order_id'])
                ->where('id', '!=', $order_detail->id)
                ->first();
            if ($order_detail2) {
                $order_detail2->update($input_order_detail2);
            }
        }


        return response()->json(['success' => 'Student information updated successfully', 'discontinued' => $contact->discontinued]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Contact::find($id)->delete();
        $this->order_clear($id);
        $this->receive_clear($id);

        return ['success' => true, 'message' => 'Deleted student successfully'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Contact::find($arr_del[$xx])->delete();
            $this->order_clear($arr_del[$xx]);
            $this->receive_clear($arr_del[$xx]);
        }

        return redirect('/contacts')->with('success', 'Deleted student successfully');
    }

    public function order_clear($id)
    {
        try {
            DB::beginTransaction();

            // Find and delete orders where 'status' is '1'
            $deletedOrderIds = Order::where('cid', $id)->pluck('id')->toArray();
            Order::where('cid', $id)->delete();

            // Delete related records from the 'order_detail' table
            if (!empty($deletedOrderIds)) {
                DB::table('order_details')->whereIn('order_id', $deletedOrderIds)->delete();
            }

            DB::commit();

            // Commit the transaction if everything was successful
        } catch (\Exception $e) {
            DB::rollBack();

            // Handle the exception or show an error message
            // For example: Log the error, return an error response, etc.
        }
    }

    public function receive_clear($id)
    {
        try {
            DB::beginTransaction();
            Receipt::where('cid', $id)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }


    public function lfind($type, $level)
    {
        //$select_list = "<option value=''>please select</option>";
        $select_list = "";
        if ($type == 'add') {
            $data = term::select(
                "terms.id as id",
                "terms.name as name",
            )

                ->where('level_id', $level)
                ->where('status', 1)
                ->orderBy("terms.name", "asc")
                ->get();

            foreach ($data as $key) {
                $select_list .= '<option value="' . $key->name . '" >' . $key->name . '</option>';
            }

            return response()->json([
                'html' =>  $select_list
            ]);
        }
    }


    public function tfind($type, $level, $term)
    {
        //$select_list = "<option value=''>please select</option>";
        $select_list = "";
        if ($type == 'add') {
            $data = bookuse::select(
                "bookuses.id as id",
                "bookuses.name as name",
            )

                ->where('level_id', $level)
                ->where('term_id', $term)
                //->where('type', 1)
                ->where('status', 1)
                ->orderBy("bookuses.name", "asc")
                ->get();

            foreach ($data as $key) {
                $select_list .= '<option value="' . $key->id . '" >' . $key->name . '</option>';
            }

            return response()->json([
                'html' =>  $select_list
            ]);
        }
    }
}
