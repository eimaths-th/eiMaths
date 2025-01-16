<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LineApiSetting;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\RolePermission\Entities\Role;
use Illuminate\Support\Facades\Crypt;

class LineApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //

        if (!Gate::allows('all-centre')) {
            $roles = Role::pluck('name', 'id')->except([1, 4])->all();
        } else {
            $roles = Role::pluck('name', 'name')->all();
        }
        $datas = LineApiSetting::all();

        $datas->each(function ($row, $index) {
            $row->no = $index + 1; // Adding serial number to each row
        });
        if ($request->ajax()) {
            //sleep(2);

            $datas = LineApiSetting::query()->orderBy('id', 'desc');
            $datas->orderBy("id", "desc")->get();
            return datatables()->of($datas)
                ->addColumn('no', function ($row) {
                    return $row->no; // Return the custom 'no' column
                })
                ->editColumn('name', function (LineApiSetting $datas) {

                    return $datas->name;
                })
                ->editColumn('line_user_id', function (LineApiSetting $datas) {

                    return $datas->line_user_id;
                })
                ->editColumn('created_at', function ($row) {

                    return $row->created_at;
                })
                ->editColumn('updated_at', function ($row) {

                    return $row->updated_at;
                })
                // ->editColumn('channel_secret', function ($row) {

                //     return $row->channel_secret;
                // })
                // ->editColumn('channel_access_token', function ($row) {
                //     return $row->channel_access_token;
                // })
                ->editColumn('create_by', function ($row) {
                    return $row->creator ? $row->creator->name : 'Unknown';
                })
                ->editColumn('update_by', function ($row) {
                    return $row->editor ? $row->editor->name : 'Unknown';
                })
                ->editColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '

                        <label class="toggle-switch">
                            <input type="checkbox" class="toggle-status-update" data-id="' . $row->id . '" ' . $checked . '>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('user-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }

                    $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';

                    return $html;
                })->rawColumns(['no', 'status', 'action'])->toJson();
        }
        $department = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $position = Position::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        return view('line_api_setting.index')
            ->with(['department' => $department])
            ->with(['position' => $position]);
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
        // Validate the incoming request
        $validatedData = $request->validate([
            'line_user_id' => 'required',
            'channel_secret' => 'required',
            'channel_access_token' => 'required',
            'name' => 'required',
        ]);

        // Encrypt the fields
        $encryptedData = [
            'line_user_id' => Crypt::encryptString($validatedData['line_user_id']),
            'channel_secret' => Crypt::encryptString($validatedData['channel_secret']),
            'channel_access_token' => Crypt::encryptString($validatedData['channel_access_token']),
            'name' => $validatedData['name']
        ]; //create_by
        $encryptedData['create_by'] = Auth::user()->id;
        // Store the encrypted data in the database (example)
        LineApiSetting::create($encryptedData);

        // Return success response
        return response()->json(['success' => 'Data stored successfully']);
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
    public function edit($id)
    {
        //

        $data = LineApiSetting::find($id);
        $line_user_id = $data->decrypt($data->line_user_id);
        $channel_secret = $data->decrypt($data->channel_secret);
        $channel_access_token = $data->decrypt($data->channel_access_token);
        return response()->json([
            'data' => $data,
            'line_user_id' => $line_user_id,
            'channel_secret' => $channel_secret,
            'channel_access_token' => $channel_access_token,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $validatedData = $request->validate([
            'name' => 'required',
            'line_user_id' => 'required',
            'channel_secret' => 'required',
            'channel_access_token' => 'required',
        ]);

        // Encrypt the fields
        $encryptedData = [
            'line_user_id' => Crypt::encryptString($validatedData['line_user_id']),
            'channel_secret' => Crypt::encryptString($validatedData['channel_secret']),
            'channel_access_token' => Crypt::encryptString($validatedData['channel_access_token']),
            'name' => $validatedData['name'],
        ]; //create_by
        $encryptedData['update_by'] = Auth::user()->id;

        $data = LineApiSetting::find($id);
        if (!$data) {
            return response()->json(['error' => 'Record not found'], 404);
        }
        $data->update($encryptedData);

        return response()->json(['message' => 'Record updated successfully', 'data' => $data]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the record
        $data = LineApiSetting::find($id);

        // Check if the record exists
        if (!$data) {
            return response()->json([
                'error' => 'Record not found.'
            ], 404);
        }

        // Attempt to delete the record
        $data->delete();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ]);
    }

    public function updateStatus(string $id, Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'status' => 'required|boolean', // Ensure status is 0 or 1
        ]);

        // If the new status is "on" (1), set all others to "off" (0)
        if ($request->status == 1) {
            LineApiSetting::where('id', '!=', $id)->update(['status' => 0]);
        }

        // Update the status of the specified record
        $data = LineApiSetting::findOrFail($id);
        $data->status = $request->status;
        $data->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }
}
