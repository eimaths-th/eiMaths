<?php

namespace App\Http\Controllers;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\level;
use App\Models\Parents;
use App\Models\Sterm;
use App\Models\studentRunningNumber;
use App\Models\term;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {

            $datass = Parents::orderBy('id', 'desc');
            if (!Gate::allows('all-centre')) {
                $datass->where('centre_id', Auth::user()->department->id);
            }
            $datas = $datass->get();
            return datatables()->of($datas)

                ->editColumn('student_name', function ($row) {
                    $stdName = '';

                    if ($row->students()->count() > 0) {
                        $stds = $row->students();

                        if ($stds->count() > 1) {
                            # code...
                            foreach ($stds as $std) {
                                $stdName .= $std->name . ' , ';
                            }
                        } else {
                            # code...
                            foreach ($stds as $std) {
                                $stdName .= $std->name;
                            }
                        }
                    } else {
                        $stdName = 'N/A';
                    }
                    return $stdName;
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('centre', function ($row) {
                    // $code= 1;
                    if ($row->department) {

                        $centre = $row->department->name;
                    } else {
                        $centre = 'N/A';
                    }
                    return $centre;
                })
                ->editColumn('parent_name', function ($row) {
                    // $code= 1;
                    if ($row->full_name) {

                        $full_name = $row->full_name;
                    } else {
                        $full_name = 'N/A';
                    }
                    return $full_name;
                })

                ->editColumn('parent_telp', function ($row) {
                    // $code= 1;
                    if ($row->telp_email) {

                        $telp_email = $row->telp_email;
                    } else {
                        $telp_email = 'N/A';
                    }
                    return $telp_email;
                })

                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    // if (Gate::allows('student-edit')) {
                    //     // $html = '<button type="button" class="btn btn-sm btn-primary btn-view" id="getViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View</button> ';

                    //     $html = '<button type="button" class="btn btn-sm btn-primary disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    // }

                    if (Gate::allows('student-edit')) {
                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }

                    if (Gate::allows('student-delete')) {
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
        $bookuse = bookuse::where([['status', '1']/* , ['type', '1'] */])
            ->orderBy("name", "asc")->get();
        $level = level::where([['status', '1']])
            ->orderBy("name", "asc")->get();

        // dd($last_book,$last_level,$graduated);
        // $histories = Histrories::all();

        return view('parents.index', [
            'centre' => $centre,
            'bookuse' => $bookuse,
            'term' => $term,
            'sterm' => $sterm,
            // 'histories' => $histories,
            'level' => $level
        ]);
        // return view('parents.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($centre)
    {
        //
        if ($centre == 0) {
            $centre = Auth::user()->department->id;
        }
        $rnumber = studentRunningNumber::pre_generate($centre);
        //dd($rnumber);
        $stds = Contact::where('centre', $centre)->get();
        $html_std = '';
        $remaining = '';

        foreach ($stds as $std) {
            $id = $std->id;
            $code = $std->code;
            $name = $std->name;
            $html_std .= '<option value="' . $id . '">' . $code . ' ' . $name . '</option>';
        }

        return response()->json([
            'running' =>  $rnumber,
            'html_std' => $html_std
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'student_id' => 'required|array',
            // 'student_id.*' => 'exists:contacts,id',
            'centre_id' => 'required',
            'fname' => 'required',
            'lname' => 'required',
            //'telp' => 'required|unique:parents|max:10|min:10',
            'telp' => 'required|max:10|min:10',
            'email' => 'required|email|unique:parents',
            'password' => 'required|min:6|confirmed',
            'address' => 'required',
            'relationship' => 'required',
            'gender' => 'required',
            //'emergency_contact' => 'required|unique:parents|max:10|min:10',
            'emergency_contact' => 'required|max:10|min:10',
        ]);
        $hashedPassword = bcrypt($request->input('password'));
        $stds = $request->input('student_id');
        $students = implode(',', $stds);

        Parents::create([
            'student_id' => $students,
            'centre_id' => $request->input('centre_id'),
            'fname' => $request->input('fname'),
            'lname' => $request->input('lname'),
            'email' => $request->input('email'),
            'password' => $hashedPassword,
            'telp' => $request->input('telp'),
            'address' => $request->input('address'),
            'relationship' => $request->input('relationship'),
            'gender' => $request->input('gender'),
            'emergency_contact' => $request->input('emergency_contact'),
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Parent account Created successfully'
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
     */
    public function edit($id)
    {
        //

        $data = Parents::find($id);

        $centre = '<option value="' . $data->department->id . '" selected> ' . $data->department->name . ' </option>';

        $stds = Contact::where('centre', $data->department->id)->get();

        $remaining = '';
        $html_std = '';
        $students_id = explode(',', $data->student_id);
        $students = Contact::whereIn('id', $students_id)->get();

        foreach ($students as $student) {
            # code...
            $html_std .= '<option value="' . $student->id . '" selected>' . $student->code . ' ' . $student->name . '</option>';
            // $html_std = '<option value="' . $student->id . '">' . $student->code . ' ' . $student->name . '</option>';
        }

        // dd($stds);
        foreach ($stds as $std) {
            $id = $std->id;
            $code = $std->code;
            $name = $std->name;
            $html_std .= '<option value="' . $id . '">' . $code . ' ' . $name . '</option>';
        }


        return response()->json(['data' => $data, 'centre' => $centre, 'html_std' => $html_std]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        // dd($request);
        if ($request->password) {
            # code...
            $request->validate([
                // Uncomment rules as needed
                'student_id' => 'required|array',
                // 'student_id.*' => 'exists:contacts,id',
                'centre_id' => 'required',
                'fname' => 'required',
                'lname' => 'required',
                //'telp' => 'required|unique:parents,telp,' . $id . '|max:10|min:10',
                'telp' => 'required|max:10|min:10',
                'email' => 'required|email|unique:parents,email,' . $id,
                'password' => 'required|min:6|confirmed',
                'address' => 'required',
                'relationship' => 'required',
                'gender' => 'required',
                //'emergency_contact' => 'required|unique:parents,emergency_contact,' . $id . '|max:10|min:10',
                'emergency_contact' => 'required|max:10|min:10',
            ]);
        } else {
            # code...
            $request->validate([
                // Uncomment rules as needed
                'student_id' => 'required|array',
                // 'student_id.*' => 'exists:contacts,id',
                'centre_id' => 'required',
                'fname' => 'required',
                'lname' => 'required',
                'telp' => 'required|unique:parents,telp,' . $id . '|max:10|min:10',
                'email' => 'required|email|unique:parents,email,' . $id,
                // 'password' => 'required|min:6|confirmed',
                'address' => 'required',
                'relationship' => 'required',
                'gender' => 'required',
                'emergency_contact' => 'required|unique:parents,emergency_contact,' . $id . '|max:10|min:10',
            ]);
        }

        $input = $request->all();
        $stds = $request->input('student_id');
        $students = implode(',', $stds);
        $parent = Parents::find($id);

        // dd($request->password);

        if ($parent) {

            $parent->update(['student_id' => $students, 'password' => bcrypt($request->password)] + $input);

        } else {
            return response()->json([
                'errors' => true,
                'message' => 'parent is does not exist'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Parent account updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
