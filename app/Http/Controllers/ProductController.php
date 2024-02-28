<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRunningNumber;
use App\Models\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        if ($request->ajax()) {
            //sleep(2);

            $datas = Product::select(
                "products.*",
                "departments.name as centre_name",
            )->join("departments", "products.centre", "=", "departments.id");
            if (!Gate::allows('all-centre')) {
                $datas->where('centre', Auth::user()->department->id);
            }

            $datas->orderBy("id", "asc")->get();
            $state_text = array('Enable', 'Disable');
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('product-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    if (Gate::allows('product-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }


        $centre = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();

        return view('products.index')->with(
            ['centre' => $centre]
        );
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
            'name.required' => 'The Accessories name must not be blank!',
            'name.unique' => 'This Accessories name already exists in the database for the selected centre!',
            'code.required' => 'The Accessories code must not be blank!',
            'code.max' => 'The Accessories code must not exceed 10 characters',
            'centre.required' => 'Please select a centre!',
            'unit.required' => 'The Accessories unit must not be blank!',
            'amount.required' => 'The Accessories Quantity must not be blank!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $input = $request->all();
        $input['code'] = ProductRunningNumber::generate($request->get('centre'));
        $input['created_by'] = Auth::id();
        Product::create($input);
        return response()->json(['success' => 'The Acceseries has been successfully added.']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data =  Product::find($id);
        return response()->json(['data' => $data]);
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
            'name.required' => 'The branch name must not be blank!',
            'name.unique' => 'This branch name already exists in the database for the selected centre!',
            'unit.required' => 'The Accessories unit must not be blank!',
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

        return response()->json(['success' => 'The Acceseries has been successfully edited.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Product::find($id)->delete();
        return ['success' => true, 'message' => 'The Acceseries has been successfully deleted.'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Product::find($arr_del[$xx])->delete();
        }

        return redirect('/products')->with('success', 'The Acceseries has been successfully deleted.');
    }
}
