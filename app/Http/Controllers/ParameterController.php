<?php

namespace App\Http\Controllers;

use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ParameterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:centre-list|centre-create|centre-edit|centre-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:centre-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:centre-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:centre-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            //sleep(2);

            $datas = parameter::orderBy("id", "asc")->get();
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })

                ->addColumn('action', function ($row) {
                    if (Gate::allows('centre-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    /*  if (Gate::allows('centre-delete')) {
                       $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                   } else {
                       $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                   } */
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }

        return view('parameter.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data =  parameter::find($id);
        return response()->json(['data' => $data]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'price' => 'required',

        ];


        $validator = Validator::make($request->all(), $rules, [
            'price.required' => 'Price must not be blank!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $data = [
            'price' => $request->get('price'),
        ];

        $update = parameter::find($id);
        $update->update($data);

        return response()->json(['success' => 'Data has been successfully edited.']);
    }
}
