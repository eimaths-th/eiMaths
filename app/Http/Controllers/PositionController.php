<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PositionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:department-list|department-create|department-edit|department-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:department-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:department-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:department-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            //sleep(2);

            $datas = Position::select(
                "positions.*",
                "departments.name as dname",
            )
                ->join("departments", "departments.id", "=", "positions.department_id");
            if (!Gate::allows('all-centre')) {
                $datas->where('department_id', Auth::user()->department->id);
            }

            $datas->orderBy("positions.id", "desc")->get();
            $state_text = [
                __('centre-management.messages.status_disabled'),
                __('centre-management.messages.status_enabled'),
            ];
            return datatables()->of($datas)
            ->filterColumn('dname',function($query,$keyword){
                $query->where('departments.name','like',"%$keyword%");
            })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('status', function ($row) use ($state_text) {
                    $state = $state_text[$row->status];
                    return $state;
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('department-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> ' . e(__('centre-management.departments.actions.edit')) . '</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="' . e(__('centre-management.messages.no_permission')) . '"><i class="fa fa-edit"></i> ' . e(__('centre-management.departments.actions.edit')) . '</button> ';
                    }
                    if (Gate::allows('department-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> ' . e(__('centre-management.departments.actions.delete')) . '</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="' . e(__('centre-management.messages.no_permission')) . '"><i class="fa fa-trash"></i> ' . e(__('centre-management.departments.actions.delete')) . '</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }

        $department = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        return view('positions.index')->with(['department' => $department]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator =  Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('positions', 'name')->where('department_id', $request->department_id)],
            'department_id' => 'required|integer|exists:departments,id',
            'status' => 'required',
            /* 'email' => 'required|string|email|max:255',
            'address' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
            'telephone' => 'required|string|max:20',*/
        ], [
            'name.required' => __('centre-management.validation.name_required'),
            'name.unique' => __('centre-management.validation.name_unique'),
            'department_id.required' => __('centre-management.validation.centre_required'),
            'status.required' => __('centre-management.validation.status_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $input = $request->all();
        Position::create($input);
        return response()->json(['success' => __('centre-management.messages.department_created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data =  Position::find($id);
        return response()->json(['data' => $data]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('positions', 'name')->where('department_id', $request->department)->ignore($id)],
            'department' => 'required|integer|exists:departments,id',
            'status' => 'required|max:10',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => __('centre-management.validation.name_required'),
            'name.unique' => __('centre-management.validation.name_unique'),
            'department.required' => __('centre-management.validation.centre_required'),
            'status.required' => __('centre-management.validation.status_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $contactd = [
            'name' => $request->get('name'),
            'department_id' => $request->get('department'),
            'status' => $request->get('status'),
        ];

        $update = Position::find($id);
        $update->update($contactd);

        return response()->json(['success' => __('centre-management.messages.department_updated')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        $position = Position::findOrFail($id);
        abort_if(\App\Models\User::where('position_id', $position->id)->exists(), 422, __('centre-management.messages.department_assigned'));
        $position->update(['status' => 0]);
        return ['success' => true, 'message' => __('centre-management.messages.department_deleted')];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            $position = Position::findOrFail($arr_del[$xx]);
            abort_if(\App\Models\User::where('position_id', $position->id)->exists(), 422, __('centre-management.messages.department_assigned'));
            $position->update(['status' => 0]);
        }

        return redirect('/positions')->with('success', __('centre-management.messages.department_deleted'));
    }
}
