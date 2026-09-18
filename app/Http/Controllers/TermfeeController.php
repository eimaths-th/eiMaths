<?php

namespace App\Http\Controllers;

use App\Models\level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class TermfeeController extends Controller
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

            $datas = level::orderBy("id", "asc")->get();
            $state_text = [__('term-fee.modal.enable'), __('term-fee.modal.disable')];
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('status', function ($row) use ($state_text) {
                    $state = $state_text[$row->status];
                    return $state;
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('centre-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> ' . e(__('term-fee.actions.edit')) . '</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="' . e(__('term-fee.messages.no_permission')) . '"><i class="fa fa-edit"></i> ' . e(__('term-fee.actions.edit')) . '</button> ';
                    }
                   /*  if (Gate::allows('centre-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    } */
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }

        return view('termfee.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data =  level::find($id);
        return response()->json(['data' => $data]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'price' => 'required',
            'half_price' => 'required',
            'book_price' => 'required',
            'book_half_price' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'price.required' => __('term-fee.validation.price_required'),
            'half_price.required' => __('term-fee.validation.half_price_required'),
            'book_price.required' => __('term-fee.validation.book_price_required'),
            'book_half_price.required' => __('term-fee.validation.book_half_price_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $data = [
            'price' => $request->get('price'),
            'half_price' => $request->get('half_price'),
            'book_price' => $request->get('book_price'),
            'book_half_price' => $request->get('book_half_price'),
        ];

        $update = level::find($id);
        $update->update($data);

        return response()->json(['success' => __('term-fee.messages.updated')]);
    }

}
