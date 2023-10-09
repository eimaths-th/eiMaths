<?php

namespace App\Http\Controllers;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\level;
use App\Models\Sterm;
use App\Models\term;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ->where('discontinued', '0');

        // $datas = Histrories::with(['contacts', 'departments'])
        //     ->select(
        //         "departments.name as centre_name",
        //         "contacts.code as code",
        //         "contacts.name as name",
        //         "contacts.start_date as start_date",
        //         "contacts.level_name as level_name",
        //         "contacts.bookuse_name as book_name",
        //         "contacts.term_name as term_name"
        //     )
        //     ->join("contacts", "histrories.student_id", "=", "contacts.id")
        //     ->join("departments", "histrories.centre", "=", "departments.id")
        //     ->where('contacts.discontinued', '0')
        //     ->get();

        // dd($datas);
        // // $query->orderBy("contacts.id", "desc");
        // // $query->orderBy("contacts.id", "desc");
        // // $datas = $query->get();
        // // dd($datas);
        // // ->where('discontinued', '0');


        // // if (!Gate::allows('all-centre')) {
        // //     $query->where('centre', Auth::user()->department->id);
        // // }

        // // // $query->orderBy("contacts.id", "desc");
        // // $datas = $query->get();
        // $query = Histrories::all();
        // dd($query);
        // dd($datas);
        if ($request->ajax()) {
            //sleep(2);



            // $query = Histrories::with(['contacts', 'departments']);

            $query = Histrories::select(
                "histrories.*",
                "departments.name as centre_name",
                "contacts.code as code",
                "contacts.name as name",
                "contacts.start_date as start_date",
                "contacts.level_name as level_name",
                "contacts.bookuse_name as book_name",
                "contacts.term_name as term_name",
            )
                ->join("contacts", "histrories.student_id", "=", "contacts.id")
                ->join("departments", "histrories.centre", "=", "departments.id")
                ->where('discontinued', '0');
            if (!Gate::allows('all-centre')) {
                $query->where('centre', Auth::user()->department->id);
            }
            $query->orderBy("id", "desc");
            $datas = $query->get();

            // dd($datas);

            // $datas = Histrories::with(['contacts', 'departments'])
            //     ->join("contacts", "histrories.student_id", "=", "contacts.id")
            //     ->join("departments", "histrories.centre", "=", "departments.id")
            //     ->where('contacts.discontinued', '0')
            //     ->when(!Gate::allows('all-centre'), function ($query) {
            //         $query->where('departments.id', Auth::user()->department->id);
            //     })
            //     ->orderBy("histrories.date", "desc")
            //     ->get();


            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('student-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
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
        $histories = Histrories::all();

        return view('histories.index', [
            'centre' => $centre,
            'bookuse' => $bookuse,
            'term' => $term,
            'sterm' => $sterm,
            'histories' => $histories,

            'level' => $level
        ]);
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
    }

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $id)
    {
        //
        dd($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        //

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
