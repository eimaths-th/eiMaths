<?php

namespace App\Http\Controllers;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Histrories;
use App\Models\histroriesTes;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Sterm;
use App\Models\studentRunningNumber;
use App\Models\term;
use Carbon\Carbon;
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

        if ($request->ajax()) {

            $datass = Histrories::orderBy('id', 'desc');
            if (!Gate::allows('all-centre')) {
                $datass->where('centre', Auth::user()->department->id);
            }

            if (!empty($request->get('sdate'))) {
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $datass->whereBetween('date', [$startDate, $endDate]);
                    }
                }
                // dd($request->all());

            }

            $datas = $datass->get();
            return datatables()->of($datas)
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
                ->editColumn('bookuse', function ($row) {
                    if ($row->booku) {
                        $bookuse = $row->booku->name;
                    } else {
                        $bookuse = $row->bookuse;
                    }
                    return $bookuse;
                })
                ->editColumn('code', function ($row) {
                    // $code= 1;
                    if ($row->student) {

                        $code = $row->student->code;
                    } else {
                        $code = 'N/A';
                    }
                    return $code;
                })
                ->editColumn('name', function ($row) {
                    // $name = 1;
                    if ($row->student) {
                        $name = $row->student->name;
                    } else {
                        $name = 'N/A';
                    }
                    return $name;
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::allows('student-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-primary btn-view" id="getViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-primary disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }

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

        return view('histories.index', [
            'centre' => $centre,
            'bookuse' => $bookuse,
            'term' => $term,
            'sterm' => $sterm,
            // 'histories' => $histories,
            'level' => $level
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function running($centre)
    // {
    //     //
    //     if ($centre == 0) {
    //         $centre = Auth::user()->department->id;
    //     }
    //     $rnumber = studentRunningNumber::pre_generate($centre);
    //     //dd($rnumber);
    //     $stds = Contact::all();
    //     $html_std = '';
    //     $remaining = '';

    //     foreach ($stds as $std) {
    //         $id = $std->id;
    //         $code = $std->code;
    //         $name = $std->name;
    //         $html_std .= '<option value="' . $id . '">' . $code . ' ' . $name . '</option>';
    //     }

    //     return response()->json([
    //         'running' =>  $rnumber,
    //         'html_std' => $html_std
    //     ]);
    // }


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

    public function stdfind($type, $std_id)
    {
        $select_list = "";
        if ($type == 'add') {
            $student = Contact::find($std_id);
            $lv1 = $student->level;
            $lv2 = $student->level2;
            $levels = level::whereBetween('id', [$lv1, $lv2])->get();
            foreach ($levels as $level) {
                $select_list .= '<option value="' . $level->id . '" >' . $level->name . '</option>';
            }

            return response()->json([
                'html' =>  $select_list
            ]);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     //
    //     $std_id = $request->input('id');
    //     $term = $request->input('term');
    //     $level_id = $request->input('level_id');
    //     $level_name = $request->input('level_name');
    //     $centre = $request->input('centre');
    //     $bookuse = $request->input('bookuse');
    //     $datetolearn = $request->input('date');
    //     $stime = $request->input('stime');
    //     $etime = $request->input('etime');
    //     $comment = $request->input('comment');
    //     $sinature = $request->input('sinature');

    //     $history = Histrories::where('student_id', $std_id)
    //         ->where('term', $term)
    //         ->where('level_id', $level_id)
    //         ->get();
    //     $count_his = count($history);

    //     if ($count_his == 0) {
    //         $start_date = $request->input('start_date');

    //         $course_remaining = 9;
    //         $start_date = Carbon::parse($start_date); // Convert start_date to Carbon instance
    //         $end_date = $start_date->addMonths(4);
    //         $history = Histrories::create([
    //             'centre' => $centre,
    //             'student_id' => $std_id,
    //             'level_id' => $level_id,
    //             'level_name' => $level_name,
    //             'term' => $term,
    //             'bookuse' => $bookuse,
    //             'date' => $datetolearn,
    //             'stime' => $stime,
    //             'etime' => $etime,
    //             'start_date' => $start_date,
    //             'end_date' => $end_date,
    //             'comment' => $comment,
    //             'sinature' => $sinature,
    //             'course_remaining' => $course_remaining,
    //         ]);
    //     } elseif ($count_his < 10) {

    //         $lastHistory = Histrories::where('student_id', $std_id)
    //             ->where('term', $term)
    //             ->where('level_id', $level_id)
    //             ->get()->orderBy('created_at', 'desc')->first();
    //         $firstHistory = Histrories::where('student_id', $std_id)
    //             ->where('term', $term)
    //             ->where('level_id', $level_id)
    //             ->get()->orderBy('created_at', 'asc')->first();
    //         if ($lastHistory) {
    //             $course_remaining = $lastHistory->course_remaining - 1;
    //         }
    //         if ($lastHistory && $firstHistory) {
    //             $start_date = $firstHistory->start_date;
    //             $end_date = $firstHistory->end_date;
    //         } else {
    //             $start_date = '0000-00-00';//error
    //             $end_date = '0000-00-00';//error
    //         }
    //         $history = Histrories::create([
    //             'centre' => $centre,
    //             'student_id' => $std_id,
    //             'level_id' => $level_id,
    //             'level_name' => $level_name,
    //             'term' => $term,
    //             'bookuse' => $bookuse,
    //             'date' => $datetolearn,
    //             'stime' => $stime,
    //             'etime' => $etime,
    //             'start_date' => $start_date, //not change from fisrt history
    //             'end_date' => $end_date, //not change from fisrt history
    //             'comment' => $comment,
    //             'sinature' => $sinature,
    //             'course_remaining' => $course_remaining, //-1 from last history
    //         ]);
    //     } else {
    //         echo 'เกินกำหนดของเทอมนี้แล้ว';
    //     }
    // }
    public function store(Request $request)
    {
        $request->validate([
            'centre' => 'required',
            'student_id' => 'required',
            'level_id' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'date' => 'required',
            'stime' => 'required',
            'etime' => 'required',
            'comment' => 'required',
            // 'signature' => 'required',
        ]);

        $centre = $request->input('centre');
        $std_id = $request->input('student_id');
        $level_id = $request->input('level_id');
        $term = $request->input('term');
        $bookuse = $request->input('bookuse');
        $datetolearn = $request->input('date');
        $stime = $request->input('stime');
        $etime = $request->input('etime');
        $comment = $request->input('comment');
        $signature = $request->input('signature');

        $level = level::find($level_id);
        $level_name = $level->name;

        $history = Histrories::where([
            ['student_id', $std_id],
            ['term', $term],
            ['level_id', $level_id]
        ])->get();
        if ($history->isEmpty()) {
            $start_date = Carbon::parse($request->input('date'));
            $end_date = $start_date->copy()->addMonths(4);
            $course_remaining = 9;
        } elseif ($history->count() < 10) {
            $lastHistory = $history->sortByDesc('created_at')->first();
            $firstHistory = $history->sortBy('created_at')->first();
            $course_remaining = $lastHistory->course_remaining - 1;
            $start_date = $firstHistory->start_date;
            $end_date = $firstHistory->end_date;
        } else {
            return response()->json(['success' => false, 'message' => 'เกินกำหนดของเทอมนี้แล้ว']);
        }
        Histrories::create([
            'centre' => $centre,
            'student_id' => $std_id,
            'level_id' => $level_id,
            'level_name' => $level_name,
            'term' => $term,
            'bookuse' => $bookuse,
            'date' => $datetolearn,
            'stime' => $stime,
            'etime' => $etime,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'comment' => $comment,
            'signature' => $signature,
            'course_remaining' => $course_remaining
        ]);

        // dd($request);

        return response()->json(['success' => true, 'message' => 'Record created successfully']);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //


        $history = Histrories::find($id);
        $data = [
            'id'                    => $history->id,
            'centre'                => $history->department->name,
            'code'                  => $history->student->code,
            'student_id'            => $history->student->name,
            'level_name'            => $history->level_name,
            'term'                  => $history->term,
            'bookuse'               => $history->booku->name,
            'course_remaining'      => $history->course_remaining,
            'date'                  => $history->date,
            'stime'                 => $history->stime,
            'etime'                 => $history->etime,
            'start_date'            => $history->start_date,
            'end_date'              => $history->end_date,
            'comment'               => $history->comment,
            'signature'             => $history->signature,
        ];
        // dd($data,$history, $id, );



        return response()->json([
            'history' => $history,
            'data' => $data,
            // 'datas' => $datas

        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $input = $request->all();
        $history = Histrories::find($id)->update($input);
        return ['success' => 'Comment student successfully'];
        // dd($request,$history);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
        $id = $request->get('id');
        $history = Histrories::find($id);

        if ($history) {
            $history->delete();
            return ['success' => true, 'message' => 'Deleted student successfully'];
        } else {
            return ['success' => false, 'message' => 'Student not found'];
        }
    }
}
