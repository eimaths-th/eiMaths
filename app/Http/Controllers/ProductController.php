<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Contact;
use App\Models\ProductRunningNumber;
use App\Models\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $datas = Product::select(
            "products.*",
            "departments.name as centre_name",
        )->join("departments", "products.centre", "=", "departments.id");
        if (!Gate::allows('all-centre')) {
            $datas->where('centre', Auth::user()->department->id);
        }

        $datas = $datas->orderBy("id", "asc")->get();
        if ($request->ajax()) {
            //sleep(2);

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('product-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-log" data-id="' . $row->id . '"><i class="fa-solid fa-eye"></i> ' . __('products.actions.log') . '</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-success disabled" data-toggle="tooltip" data-placement="bottom" title="' . __('common.feedback.permission_denied') . '"><i class="fa-solid fa-eye"></i> ' . __('products.actions.log') . '</button> ';
                    }
                    if (Gate::allows('product-edit')) {
                        $html .= '<button type="button" class="btn btn-sm btn-info btn-barcode" data-id="' . $row->id . '"><i class="fa-solid fa-barcode"></i> ' . __('products.actions.barcode') . '</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-info disabled" data-toggle="tooltip" data-placement="bottom" title="' . __('common.feedback.permission_denied') . '"><i class="fa-solid fa-barcode"></i> ' . __('products.actions.barcode') . '</button> ';
                    }

                    if (Gate::allows('product-edit')) {
                        $html .= '<button type="button" class="btn btn-sm btn-info btn-qbarcode" data-id="' . $row->id . '"><i class="fa-solid fa-qrcode"></i> ' . __('products.actions.qrcode') . '</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-info disabled" data-toggle="tooltip" data-placement="bottom" title="' . __('common.feedback.permission_denied') . '"><i class="fa-solid fa-qrcode"></i> ' . __('products.actions.qrcode') . '</button> ';
                    }

                    if (Gate::allows('product-edit')) {
                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' . $row->id . '"><i class="fa fa-edit"></i> ' . __('products.actions.edit') . '</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="' . __('common.feedback.permission_denied') . '"><i class="fa fa-edit"></i> ' . __('products.actions.edit') . '</button> ';
                    }
                    if (Gate::allows('product-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> ' . __('products.actions.delete') . '</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="' . __('common.feedback.permission_denied') . '"><i class="fa fa-trash"></i> ' . __('products.actions.delete') . '</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }

        $centre = Department::where([['status', '1']])
            ->whereNot('id', 4)
            ->whereNot('id', 5)
            ->orderBy("name", "asc")->get();
        return view('products.index')->with([
            'centre' => $centre,
            'data' => $datas
        ]);
    }

    public function printProduct(Request $request)
    {
        //
        $type = $request->type;
        $centre = $request->centre;
        $code_type  = $request->code_type;
        if ($type == 'view') {
            $view = 'products.view';
        }
        if ($type == 'print') {
            $view = 'products.print';
        }

        if ($centre == 'all') {
            $products = Product::all()->groupBy('centre');
        } else {
            $products = Product::where('centre', $centre)->get()->groupBy('centre');
        }

        return view($view)->with([
            'type' => $type,
            'products' => $products,
            'code_type' => $code_type,
        ]);
    }


    public function getDes($centre, $type)
    {
        //
        if ($centre == 0) {
            $centre = Auth::user()->department->id;
        }
        if ($type == 1) {
            $stds = Contact::where('centre', $centre)->get();
        } else {
            $stds = Department::where('id', '!=', $centre)->get();
        }

        $html_std = '';
        $remaining = '';

        foreach ($stds as $std) {
            $id = $std->id;
            $code = $std->code;
            $name = $std->name;
            $html_std .= '<option value="' . $id . '">' . $code . ' ' . $name . '</option>';
        }

        return response()->json([
            'html_std' => $html_std
        ]);
    }


    public function pfind($pid)
    {
        $product = Product::where('code', $pid)->first();

        if (!$product) {
            return response()->json(['message' => __('products.messages.product_not_found')]);
        }

        return response()->json(['product' =>  $product]);
    }

    public function getStock(Request $request)
    {
        // Retrieve the parameter from the request
        $Id = $request->input('id');
        abort_unless(is_scalar($Id) && ctype_digit((string) $Id), 422);
        // Stock's joined query does not inherit Product's branch scope.
        Product::query()->findOrFail($Id);

        $product = Stock::select(
            'stocks.*',
            'stocks.created_at as created_date',
            'users.name as user_name',
            "stock_departments.name as centre_name",
            "products.name as product_name",
        )
            ->join('users', 'stocks.user_id', '=', 'users.id')
            ->join("products", "stocks.product_id", "=", "products.id")
            ->join("departments as stock_departments", "stocks.centre", "=", "stock_departments.id")
            ->where('stocks.product_id', $Id);

        //Conditional join based on stocks.type
        $product->leftJoin("contacts", function ($join) {
            $join->on("stocks.student_id", "=", "contacts.id")
                ->where("stocks.type", "=", 1)
                ->whereNotNull("stocks.student_id");
        });
        $product->leftJoin("departments as destination_departments", function ($join) {
            $join->on("stocks.student_id", "=", "destination_departments.id")
                ->where("stocks.type", "=", 2)
                ->whereNotNull("stocks.student_id");
        });

        // Select appropriate column based on stocks.type
        $product->addSelect(DB::raw("CASE WHEN stocks.type = 1 AND stocks.student_id IS NOT NULL THEN contacts.name WHEN stocks.type = 2 AND stocks.student_id IS NOT NULL THEN destination_departments.name ELSE NULL END as student_or_centre_name"));
        $product->addSelect(DB::raw("CASE WHEN stocks.type = 1 AND stocks.student_id IS NOT NULL THEN 'Student' WHEN stocks.type = 2 AND stocks.student_id IS NOT NULL THEN 'Centre' ELSE NULL END as student_or_centre_type"));


        $product = $product->orderBy("stocks.id", "desc")->get();

        return DataTables::of($product)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($centre)
    {
        if ($centre == 0) {
            $centre = Auth::user()->department->id;
        }
        $rnumber = ProductRunningNumber::pre_generate($centre);
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('centre', $request->input('centre'));
                }),
            ],
            'code' => 'required|string|max:10',
            'centre' => 'required',
            'unit' => 'required',
            'amount' => 'required',
        ], [
            'name.required' => __('products.validation.name_required'),
            'name.unique' => __('products.validation.name_unique'),
            'code.required' => __('products.validation.code_required'),
            'code.max' => __('products.validation.code_max'),
            'centre.required' => __('products.validation.centre_required'),
            'unit.required' => __('products.validation.unit_required'),
            'amount.required' => __('products.validation.amount_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $input = $request->all();
        $input['code'] = ProductRunningNumber::generate($request->get('centre'));
        $input['created_by'] = Auth::id();
        Product::create($input);
        return response()->json(['success' => __('products.messages.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => __('products.messages.product_not_found')], 404);
        }

        return response()->json([
            'data' => $product,
        ]);
    }

    public function barcode($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => __('products.messages.product_not_found')], 404);
        }

        $barcode = new DNS1D();
        $barcodeData = $barcode->getBarcodeHTML($product->code, 'C39', 1, 50, 'black', false);

        return response()->json([
            'barcode' => $barcodeData,
        ]);
    }

    public function qbarcode($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => __('products.messages.product_not_found')], 404);
        }

        $qbarcode = new DNS2D();
        $qbarcodeData = $qbarcode->getBarcodeHTML($product->code, 'QRCODE', 5, 5, 'black', false);

        return response()->json([
            'qbarcode' => $qbarcodeData,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($id, $request) {
                    return $query->where('id', '!=', $id)
                        ->where('name', $request->input('name'))
                        ->where('centre', $request->input('centre'));
                }),
            ],
            'unit' => 'required',
        ];

        $messages = [
            'name.required' => __('products.validation.update_name_required'),
            'name.unique' => __('products.validation.update_name_unique'),
            'unit.required' => __('products.validation.unit_required'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $contactd = [
            'name' => $request->get('name'),
            'unit' => $request->get('unit'),
            'detail' => $request->get('detail'),
        ];

        $update = Product::find($id);
        $update->update($contactd);

        return response()->json(['success' => __('products.messages.updated')]);
    }


    public function add_stock(Request $request, $id)
    {
        return $this->adjustStock($request, $id, false);
    }

    public function rm_stock(Request $request, $id)
    {
        return $this->adjustStock($request, $id, true);
    }

    private function adjustStock(Request $request, $id, bool $remove)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000',
            'student' => 'nullable|integer|min:1|required_with:type',
            'type' => 'nullable|integer|in:1,2|required_with:student',
        ]);
        $product = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $id, $remove, $request) {
            $product = Product::query()->lockForUpdate()->findOrFail($id);
            \App\Support\Branch\BranchAccess::assertBranch($request->user(), $product->centre, true);
            if (!empty($data['student'])) {
                if ((int) $data['type'] === 1) {
                    abort_unless(
                        Contact::where('centre', $product->centre)->whereKey($data['student'])->exists(),
                        422,
                        __('products.validation.student_branch')
                    );
                } else {
                    abort_if((int) $data['student'] === (int) $product->centre, 422, __('products.validation.destination_different'));
                    abort_unless(
                        Department::whereKey($data['student'])->where('status', 1)->exists(),
                        422,
                        __('products.validation.destination_invalid')
                    );
                }
            }
            abort_if($remove && $data['amount'] > $product->amount, 422, __('products.validation.insufficient_stock'));
            $product->amount = $product->amount + ($remove ? -$data['amount'] : $data['amount']);
            $product->save();
            Stock::create([
                'product_id' => $product->id, 'centre' => $product->centre, 'type' => $data['type'] ?? null,
                'student_id' => $data['student'] ?? null, 'user_id' => $request->user()->id,
                'add_stock' => $remove ? 0 : $data['amount'], 'rm_stock' => $remove ? $data['amount'] : 0,
                'in_stock' => $product->amount,
            ]);
            return $product;
        });
        return response()->json(['success' => __('products.messages.stock_saved'), 'product' => $product]);
    }

    public function rm_stock_centre(Request $request, $id)
    {
        return $this->adjustStock($request, $id, true);
    }


    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Product::find($id)->delete();
        return ['success' => true, 'message' => __('products.messages.deleted')];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Product::find($arr_del[$xx])->delete();
        }

        return redirect('/products')->with('success', __('products.messages.deleted'));
    }
}
