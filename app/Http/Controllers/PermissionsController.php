<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        if ($request->ajax()) {
            //sleep(2);



            $datas = Permission::orderBy("id","desc")->get();
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('role', function ($row) {
                    $rpermission = DB::table('role_has_permissions')->where('role_id', $row->id)
                        ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                        ->orderBy("permission_id", "asc")
                        ->pluck('permissions.name');
                    $tperm = '';
                    foreach ($rpermission as $perm) {
                        $tperm .= ucfirst($perm) . ',';
                    }
                    return $tperm;
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('role-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    if (Gate::allows('role-delete')) {
                        // $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        // $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }
        $permission = Permission::get();
        return view('permissions.index', compact('permission'));
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
            'name' => 'required|string|max:255|unique:roles',
            // 'permission' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $permission = Permission::create(['name' => $request->input('name')]);
        // $role->syncPermissions($request->input('permission'));

        /* return redirect()->route('roles.index')
            ->with('success', 'Role created successfully'); */
        return response()->json(['success' => 'Add Permission Success']);
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
     */
    public function edit(string $id)
    {
        //
        $data = Permission::find($id);

        return response()->json(['name' => $data->name]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $rules = [
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
        ];


        $validator =  Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $input = $request->all();
        $permission = Permission::find($id);
        $permission->update($input);

        /* return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully'); */
        return response()->json(['success' => 'Save Role Success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
