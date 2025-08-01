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

class DepartmentController extends Controller
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

            $datas = Department::orderBy("id", "desc");
            //ยกเว้น id = 4,5
            $datas->whereNotIn('id', [4, 5]);
            if (!Gate::allows('all-centre')) {
                $datas->where('id', Auth::user()->department->id);
            }
            $datas->get();
            $state_text = array('Discontinued', 'Active');
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
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    if (Gate::allows('centre-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }

        return view('departments.index');
    }


    public function find($type, $department)
    {
        $select_list = "<option value=''>please select</option>";
        if ($type == 'add') {
            $data = Position::select(
                "positions.id as id",
                "positions.name as name",
            )

                ->where('department_id', $department)
                ->where('status', 1)
                ->orderBy("positions.name", "asc")
                ->get();

            foreach ($data as $key) {
                $select_list .= '<option value="' . $key->id . '" >' . $key->name . '</option>';
            }

            return response()->json([
                'html' =>  $select_list
            ]);
        }
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
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'required|string|max:10',
            'status' => 'required',
            /* 'email' => 'required|string|email|max:255',
            'address' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
            'telephone' => 'required|string|max:20',*/
        ], [
            'name.required' => 'The branch name must not be blank!',
            'name.unique' => 'This branch name already exists in the database!',
            'code.required' => 'The branch code must not be blank!',
            'code.max' => 'The branch code must not exceed 10 characters',
            'status.required' => 'Please select a status!',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $input = $request->all();
        Department::create($input);
        return response()->json(['success' => 'The branch has been successfully added.']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data =  Department::find($id);
        return response()->json(['data' => $data]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'code' => 'required|max:10',
            'status' => 'required|max:10',

        ];


        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'The branch name must not be blank!',
            'name.unique' => 'This branch name already exists in the database!',
            'code.required' => 'The branch code must not be blank!',
            'code.max' => 'The branch code must not exceed 10 characters!',
            'status.required' => 'Please select a status!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $contactd = [
            'name' => $request->get('name'),
            'code' => $request->get('code'),
            'status' => $request->get('status'),
        ];

        $update = Department::find($id);
        $update->update($contactd);

        return response()->json(['success' => 'The branch has been successfully edited.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Department::find($id)->delete();
        return ['success' => true, 'message' => 'The branch has been successfully deleted.'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Department::find($arr_del[$xx])->delete();
        }

        return redirect('/departments')->with('success', 'The branch has been successfully deleted.');
    }
}
