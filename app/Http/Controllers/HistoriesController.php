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
        $levels = level::all();
        $bookuses = bookuse::all();
        $last_level = $levels->last();
        $last_book = $bookuses->last();
        if ($request->ajax()) {
            //sleep(2);
            //dd($request->get('approved'));

            $query = Contact::select(
                "contacts.*",
                "departments.name as centre_name",
                "levels.name as level_name",
                "bookuses.name as book_name",
                "start_terms.name as start_term_name",
                "pterms.name as term_name"
            )
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("levels", "contacts.level", "=", "levels.id")
                ->join("bookuses", "contacts.bookuse", "=", "bookuses.id")
                ->join("terms as start_terms", "contacts.start_term", "=", "start_terms.id")
                ->join("terms as pterms", "contacts.term", "=", "pterms.id")
                ->where('discontinued', '0')
                ->where('level', $last_level->id)
                ->where('bookuse', $last_book->id);
            if (!Gate::allows('all-centre')) {
                $query->where('centre', Auth::user()->department->id);
            }

            $query->orderBy("contacts.id", "desc");
            $datas = $query->get();

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
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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
