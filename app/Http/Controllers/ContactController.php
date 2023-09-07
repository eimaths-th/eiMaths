<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\Product;
use App\Models\studentRunningNumber;
use App\Models\term;
use App\Models\Sterm;
use App\Models\Order;
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
        $this->middleware('permission:contact-list|contact-create|contact-edit|contact-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:contact-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:contact-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:contact-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            //sleep(2);

            $query = Contact::select(
                "contacts.*",
                "departments.name as centre_name",
                "levels.name as level_name",
                "products.name as book_name",
                "start_terms.name as start_term_name",
                "pterms.name as term_name"
            )
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("levels", "contacts.level", "=", "levels.id")
                ->join("products", "contacts.bookuse", "=", "products.id")
                ->join("terms as start_terms", "contacts.start_term", "=", "start_terms.id")
                ->join("terms as pterms", "contacts.term", "=", "pterms.id")
                ->where('discontinued', '0');


            // Handle date range filtering
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            dd($request->input('dateRange'));
            //if ($startDate && $endDate) {
            if ($request->input('filter')) {
                $query->whereBetween('contacts.start_date', [$request->input('filter'), $request->input('filter')]);
            }
            $query->orderBy("contacts.id", "desc");
            $datas = $query->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('contact-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    if (Gate::allows('contact-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action', 'more'])->toJson();
        }

        $centre = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $term = term::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $sterm = Sterm::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $bookuse = Product::where([['status', '1'], ['type', '1']])
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
    public function create()
    {
        $rnumber = studentRunningNumber::pre_generate(Auth::user()->department->id);
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

        $priceq = Product::find($request->get('bookuse'));
        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));

        $input = $request->all();
        $input['code'] = studentRunningNumber::generate($request->get('centre'));
        $input['level_name'] = $levelq->name;
        $input['term_name'] = $termq->name;
        $input['bookuse_name'] = $priceq->name;

        $contact = Contact::create($input);

        //dd($priceq);

        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $contact->id;
        $input_order['order_number'] = OrderRunningNumber::generate($request->get('centre'));
        $input_order['total_price'] = $priceq->price;
        $order = Order::create($input_order);

        /* $contact->update([
            'order' => $order->id,
        ]); */
        $contact->order = $order->id;
        $contact->save();

        $input_order_detail['order_id'] = $order->id;
        $input_order_detail['product_id'] = $priceq->id;
        $input_order_detail['pname'] = $priceq->name;
        $input_order_detail['punit'] = $priceq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $priceq->price;
        $order = OrderDetail::create($input_order_detail);

        $select_list_contact = '<option value="' . $contact->id . '" > ' . $contact->name . '</option>';
        return response()->json([
            'success' => 'Added student information successfully.',
            'contact' => $select_list_contact, 'cid' => $contact->id
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data = Contact::find($id);
        return response()->json(['data' => $data]);
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

        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));
        $priceq = Product::find($request->get('bookuse'));

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
            'bookuse_name' => $priceq->name,
            'discontinued' => $request->get('discontinued'),
            'discontinued_date' => $request->get('discontinued_date'),
            'discontinued_reason' => $request->get('discontinued_reason'),
        ];

        $contact = Contact::find($id);
        $contact->update($contactd);

        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $contact->id;
        $input_order['total_price'] = $priceq->price;
        $order = Order::find($contact->order);

        if ($order) {
            $order->update($input_order);
        }

        $input_order_detail['order_id'] = $order->id ?? null; // Set to null if $order is not found
        $input_order_detail['product_id'] = $priceq->id;
        $input_order_detail['pname'] = $priceq->name;
        $input_order_detail['punit'] = $priceq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $priceq->price;
        $order_detail = OrderDetail::where('order_id', $input_order_detail['order_id'])->first();

        if ($order_detail) {
            $order_detail->update($input_order_detail);
        }

        return response()->json(['success' => 'Student information updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Contact::find($id)->delete();
        $this->order_clear($id);

        return ['success' => true, 'message' => 'Deleted student successfully'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Contact::find($arr_del[$xx])->delete();
            $this->order_clear($arr_del[$xx]);
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
            $data = Product::select(
                "products.id as id",
                "products.name as name",
            )

                ->where('level_id', $level)
                ->where('term_id', $term)
                ->where('type', 1)
                ->where('status', 1)
                ->orderBy("products.name", "asc")
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
