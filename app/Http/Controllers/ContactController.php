<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\studentRunningNumber;
use App\Models\term;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
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

            $datas = Contact::select(
                "contacts.*",
                "departments.name as centre_name",
                "levels.name as level_name",
                "start_terms.name as start_term_name",
                "pterms.name as term_name"
            )
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("levels", "contacts.level", "=", "levels.id")
                ->join("terms as start_terms", "contacts.start_term", "=", "start_terms.id")
                ->join("terms as pterms", "contacts.term", "=", "pterms.id")
                ->orderBy("contacts.id", "desc")
                ->get();
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
                })->rawColumns(['checkbox', 'action'])->toJson();
        }

        $centre = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $term = term::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $bookuse = bookuse::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $level = level::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        //dd($term);

        return view('contacts.index')->with(['centre' => $centre])
            ->with(['bookuse' => $bookuse])
            ->with(['level' => $level])
            ->with(['term' => $term]);
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
            //'postcode' => 'int|max:10',
            /* 'email' => 'required|string|email|max:255',
            'address' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
            'telephone' => 'required|string|max:20',*/
        ], [
            'name.required' => 'Student name must not be empty.',
            'name.unique' => 'This student name already exists in the database.',
            'start_date.required' => 'Please provide a start date.',
            'start_term.required' => 'Please select a start term.',
            'school.required' => 'Please provide the school name.',
            'level.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
            //'postcode.int' => 'The postcode must be a valid integer.',
            //'postcode.max' => 'The postcode must not exceed :max characters.',
            /* 'email.required' => 'Email address must not be empty.',
            'email.email' => 'Please enter a valid email address.',
            'address.required' => 'Address must not be empty.',
            'postcode.required' => 'Postcode must not be empty.',
            'postcode.max' => 'The postcode must not exceed :max characters.',
            'telephone.required' => 'Telephone number must not be empty.',
            'telephone.max' => 'The telephone number must not exceed :max characters.', */
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $input = $request->all();
        $input['code'] = studentRunningNumber::generate($request->get('centre'));

        $contact = Contact::create($input);
        $select_list_contact = '<option value="' . $contact->id . '" > ' . $contact->name . '</option>';
        return response()->json(['success' => 'Added student information successfully.', 'contact' => $select_list_contact, 'cid' => $contact->id]);
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
            'father_name' => $request->get('father_name'),
            'father_email' => $request->get('father_email'),
            'father_mobile' => $request->get('father_mobile'),
            'mother_name' => $request->get('mother_name'),
            'mother_email' => $request->get('mother_email'),
            'mother_mobile' => $request->get('mother_mobile'),
            'level' => $request->get('level'),
            'term' => $request->get('term'),
            'bookuse' => $request->get('bookuse'),
            'discontinued' => $request->get('discontinued'),
            'discontinued_date' => $request->get('discontinued_date'),
            'discontinued_reason' => $request->get('discontinued_reason'),
        ];

        $contact = Contact::find($id);
        $contact->update($contactd);

        return response()->json(['success' => 'Student information updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Contact::find($id)->delete();
        return ['success' => true, 'message' => 'Deleted student successfully'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Contact::find($arr_del[$xx])->delete();
        }

        return redirect('/contacts')->with('success', 'Deleted student successfully');
    }
}
