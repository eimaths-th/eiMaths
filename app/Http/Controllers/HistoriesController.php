<?php

namespace App\Http\Controllers;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\FileUpload;
use App\Models\HistoryStudentImg;
use App\Models\Histrories;
use App\Models\histroriesTes;
use App\Models\level;
use App\Models\LogHistory;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Sterm;
use App\Models\studentRunningNumber;
use App\Models\term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class HistoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function formatDate($date)
    {
        return date('d-m-Y', strtotime($date));
    }


    public function checkStartEndDate($std_id, $term, $level_id)
    {
        $history = Histrories::where([
            ['student_id', $std_id],
            ['term', $term],
            ['level_id', $level_id]
        ])->get();
        // dd( $history->count(),$std_id,$term,$level_id,$history,);
        //$lastHistory = $history->sortByDesc('created_at')->first();
        $firstHistory = $history->sortBy('date')->first();

        //dd($firstHistory);

        if ($firstHistory) {
            $start_date = Carbon::parse($firstHistory->date);
            $end_date = $start_date->copy()->addMonths(4);
            $startDate = $this->formatDate($start_date);
            $endDate = $this->formatDate($end_date);
            return $startDate . ' - ' . $endDate;
        } else {
            return '-'; // or you can return some default value if no record is found.
        }
    }


    public function index(Request $request)
    {

        if ($request->ajax()) {

            $datass = Histrories::orderBy('date', 'desc');
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
                    /* if ($row->booku) {
                        $bookuse = $row->booku->name;
                    } else { */
                    $bookuse = $row->bookuse;
                    //}
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
                ->editColumn('start_date', function ($row) {
                    $code = $this->checkStartEndDate($row->student_id, $row->term, $row->level_id);
                    return $code;
                })
                ->editColumn('name', function ($row) {
                    // $name = 1;
                    $nic = $row->contact_nickname !== null && $row->contact_nickname !== '' ? '(' . $row->contact_nickname . ')' : '';
                    if ($row->student) {
                        $name = $row->student->name;
                    } else {
                        $name = 'N/A';
                    }
                    return $name . ' ' . $nic;
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::allows('student-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-primary btn-view" id="getViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View </button> ';
                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit </button> ';
                        // $html .= '<button type="button" class="btn btn-sm btn-info btn-edit" id="getLogData" data-id="' . $row->id . '"><i class="fa fa-clipboard"></i> Log </button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-primary disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> View</button> ';
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    if (Gate::allows('view-log')) {

                        $html .= '<button type="button" class="btn btn-sm btn-info btn-edit" id="getLogData" data-id="' . $row->id . '"><i class="fa fa-clipboard"></i> Log </button> ';
                    } else {

                        $html .= '<button type="button" class="btn btn-sm btn-info btn-edit" id="getLogData" disabled><i class="fa fa-clipboard"></i> Log </button> ';
                    }

                    // if (Gate::allows('student-edit')) {
                    //     $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    // } else {
                    //     $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    // }

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

        $role = 'Teacher';
        $teacher = User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })->get();

        // dd($last_book,$last_level,$graduated);
        // $histories = Histrories::all();

        return view('histories.index', [
            'centre' => $centre,
            'bookuse' => $bookuse,
            'term' => $term,
            'sterm' => $sterm,
            // 'histories' => $histories,
            'level' => $level,
            'lteacher' => $teacher
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
        // dd($request);
        $validator = Validator::make($request->all(), [
            'centre' => 'required',
            'student_id' => 'required',
            'level_id' => 'required',
            'teacher_id' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'date' => 'required',
            'stime' => 'required',
            'etime' => 'required',
            'comment' => 'required',
            // 'signature' => 'required',
        ], [
            'centre.required' => 'Centre must not be empty.',
            'student_id.required' => 'Student must not be empty.',
            'teacher_id.required' => 'Teacher must not be empty.',
            'date.required' => 'Please provide date.',
            'stime.required' => 'Please provide start tine.',
            'etime.required' => 'Please provide end time.',
            'comment.required' => 'Please provide comment.',
            'level_id.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }


        $centre = $request->input('centre');
        $std_id = $request->input('student_id');
        $teacher_id = $request->input('teacher_id');
        $level_id = $request->input('level_id');
        $term = $request->input('term');
        $bookuse = $request->input('bookuse');
        $datetolearn = $request->input('date');
        $stime = $request->input('stime');
        $etime = $request->input('etime');
        $comment = $request->input('comment');
        //$signature = $request->input('signature');

        $level = level::find($level_id);
        $level_name = $level->name;

        //contact data
        $contact = Contact::find($std_id);

        $history = Histrories::where([
            ['student_id', $std_id],
            ['term', $term],
            ['level_id', $level_id]
        ])->get();
        // dd( $history->count(),$std_id,$term,$level_id,$history,);
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

            if ($contact->level2 == $level_id && $contact->term2 == $term && $course_remaining == 0) {
                //auto discontinue
                $contactd = [
                    'discontinued' => 1,
                    'discontinued_date' => date("Y-m-d"),
                    'discontinued_reason' => 'End of course',
                ];

                $contact->update($contactd);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'เกินกำหนดของเทอมนี้แล้ว']);
        }


        /*  if (!empty($expen->signature)) {
            $fileToDelete = public_path() . $expen->signature;
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
        } */
        $signatureData = $request->input('signature');
        $encodedData = str_replace('data:image/png;base64,', '', $signatureData);
        $decodedData = base64_decode($encodedData);
        $sign_name = uniqid() . '.png';
        $sign_pname = '/file_upload/' . $sign_name;
        $signpath = public_path() . $sign_pname;
        File::put($signpath, $decodedData);

        $history = Histrories::create([
            'centre' => $centre,
            'teacher_id' => $teacher_id,
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
            'signature' => $sign_name,
            'course_remaining' => $course_remaining
        ]);

        $filesb = $request->get('img');
        if ($filesb !== null) {
            $filebs = "";
            foreach ($filesb as $fileb) {
                //echo $fileb;

                $oldfile = FileUpload::where('oldname', $fileb)->get();
                $filebs = $oldfile[0]->filename;
                //File::move(public_path() . '/file_upload/' . $oldfile[0]->filename, public_path() . '/file_upload/' . $filebs);
                //FileUpload::where('filename', $oldfile[0]->filename)->delete();

                HistoryStudentImg::create([

                    'history_id' => $history->id,
                    'student_id' => $std_id,
                    'img' => $filebs,

                ]);
            }
        }




        // dd($request);

        return response()->json(['success' => true, 'message' => 'Record created successfully']);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        //dd($id);
        $history = Histrories::find($id);
        $images = HistoryStudentImg::where('history_id', $id)->get();
        $centre = Department::where('id', $history->centre)->get();
        $teacher = User::where('id', $history->teacher_id)->first();
        if ($teacher !== null) {
            $teacher_name = $teacher->name;
        } else {
            $teacher_name = 'ไม่มีการบันทึกครูผู้สอน';
        }

        $img_html = '';
        $slide_html = '';
        $i = 1;
        if (!empty($images)) {
            foreach ($images as $img) {
                // Assuming the image filename is stored in the 'image_filename' column

                if (substr($img->img, -1) === '|') {
                    $img->img = rtrim($img->img, '|');
                }
                if ($i == 1) {
                    $imgac = "active";
                } else {
                    $imgac = "";
                }
                $slide_html .= ' <li data-target="#carouselExampleIndicators" data-slide-to="' . $i . '"
            class="' . $imgac . '"></li>';
                $img_html .= ' <div class="carousel-item ' . $imgac . '">
            <img class="d-block w-100"
                src="' . asset('file_upload/' . $img->img) . '"
                alt="' . $img->id . '"></div>';
                $i++;
            }
        }

        return response()->json([
            'history' => $history,
            'centre' => $history->department->name,
            'student' => $history->student->student_code,
            'teacher' => $teacher_name,
            'slide_html' => $slide_html,
            'img_html' => $img_html,  // Include the generated HTML in the JSON response
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $history = Histrories::find($id);
        $teacher = User::where('id', $history->teacher_id)->first();
        if ($teacher !== null) {
            $teacher_name = $teacher->name;
            $teacher_id = $teacher->id;
        } else {
            $teacher_name = 'ไม่มีการบันทึกครูผู้สอน';
            $teacher_id = '';
        }


        $data = [
            'id'                    => $history->id,
            'centre'                => $history->department->name,
            'code'                  => $history->student->code,
            'student_id'            => $history->student->name,
            'teacher'               => $teacher_name,
            'teacher_id'            => $teacher_id,
            'level_name'            => $history->level_name,
            'term'                  => $history->term,
            'bookuse'               => $history->bookuse,
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
    public function log($id)
    {
        //
        $histories = LogHistory::where('history_id', $id)->get();


        $html = '';

        if ($histories->count() > 0) {
            # code...
            $html .= '
            <div class="row mb-3">
            <div class="col-xs-3 col-sm-3 col-md-3">
                <div class="form-group">
                    <strong><i class="fas fa-code"></i> Centre :</strong>
                    <span class="text-info"> ' . $histories->first()->history->department->name . ' </span>

                </div>
            </div>

            <div class="col-xs-3 col-sm-3 col-md-3">
                <div class="form-group">
                    <strong><i class="fas fa-code"></i> Code : </strong>

                    <span class="text-info">' . $histories->first()->history->student->code . '</span>
                </div>
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3">
                <div class="form-group">
                    <strong><i class="fas fa-code"></i> Student : </strong>
                    <span class="text-info" for="" >' . $histories->first()->history->student->name . '</span>

                </div>
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3">
                <div class="form-group">
                <strong><i class="fas fa-code"></i> Nick Name : </strong>
                <span class="text-info" for="" >' . $histories->first()->history->student->nickname . '</span>
                </div>
            </div>
            </div>
            ';
            foreach ($histories as $key => $historys) {
                # code...
                $html .= '

                <label for="" > ' . $key + 1 . '. Edited : ' . $historys->updated_at . '</label> <br>
                <label for="" >Old History</label>
                    <div class="row mb-3">
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Level : </strong>
                                <span class="text-danger" for="" >' . $historys->level_name_old . '</span>
                            </div>
                        </div>

                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Term : </strong>
                                <span class="text-danger" for="" >' . $historys->term_old . '</span>
                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Bookuse : </strong>
                                <span class="text-danger" for="" >' . $historys->bookuse_old . '</span>

                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group text-nowrap">
                                <strong><i class="fas fa-code"></i> Course Remaining : </strong>
                                <span class="text-danger" for="" >' . $historys->course_remaining_old . '</span>


                            </div>
                        </div>

                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Date : </strong>
                                <span class="text-danger" for="" >' . $historys->date_old . '</span>
                            </div>
                        </div>

                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Start Time : </strong>
                                <span class="text-danger" for="" >' . $historys->stime_old . '</span>
                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> End Time : </strong>
                                <span class="text-danger" for="" >' . $historys->etime_old . '</span>

                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Comment : </strong>
                                <span class="text-danger" for="" >' . $historys->comment_old . '</span>
                            </div>
                        </div>
                    </div>

                    <label for="" >New History</label>
                    <div class="row mb-3">
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Level : </strong>
                                <span class="text-success" for="" >' . $historys->level_name_new . '</span>
                            </div>
                        </div>

                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Term : </strong>
                                <span class="text-success" for="" >' . $historys->term_new . '</span>
                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Bookuse : </strong>
                                <span class="text-success" for="" >' . $historys->bookuse_new . '</span>

                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group text-nowrap">
                                <strong><i class="fas fa-code"></i> Course Remaining : </strong>
                                <span class="text-success" for="" >' . $historys->course_remaining_new . '</span>


                            </div>
                        </div>

                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Date : </strong>
                                <span class="text-success" for="" >' . $historys->date_new . '</span>
                            </div>
                        </div>

                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Start Time : </strong>
                                <span class="text-success" for="" >' . $historys->stime_new . '</span>
                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> End Time : </strong>
                                <span class="text-success" for="" >' . $historys->etime_new . '</span>

                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Comment : </strong>
                                <span class="text-success" for="" >' . $historys->comment_new . '</span>
                            </div>
                        </div>
                    </div>

                    <label for="" class="breadcrumb float-sm-right"> Approve By : ' . $histories->first()->approve_name . '</label> <br>

                    <div class="modal-footer"></div>
                ';
            }
        } else {
            $html = 'Noting Edit';
        }

        // dd($histories, $id);
        return response()->json([
            'html' => $html
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $history = Histrories::find($id);
        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'level_id' => 'required',
            'teacher_id' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'date' => 'required',
            'stime' => 'required',
            'etime' => 'required',
            'comment' => 'required',
            // 'signature' => 'required',
        ], [
            'teacher_id.required' => 'Teacher must not be empty.',
            'date.required' => 'Please provide date.',
            'stime.required' => 'Please provide start tine.',
            'etime.required' => 'Please provide end time.',
            'comment.required' => 'Please provide comment.',
            'level_id.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }


        if (!empty($history->signature)) {
            $fileToDelete = public_path() .  '/file_upload/'  . $history->signature;
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
        }

        $signatureData = $request->input('signature');
        $encodedData = str_replace('data:image/png;base64,', '', $signatureData);
        $decodedData = base64_decode($encodedData);
        $sign_name = uniqid() . '.png';
        $sign_pname = '/file_upload/' . $sign_name;
        $signpath = public_path() . $sign_pname;
        File::put($signpath, $decodedData);

        $logHistory = LogHistory::create([
            'history_id' => $history->id,
            'centre' => $history->centre,
            'student_id' => $history->student_id,
            'status' => 'edit',
            'level_id_old' => $history->level_id,
            'level_name_old' => $history->level_name,
            'term_old' => $history->term,
            'bookuse_old' => $history->bookuse,
            'date_old' => $history->date,
            'stime_old' => $history->stime,
            'etime_old' => $history->etime,
            'comment_old' => $history->comment,
            'course_remaining_old' => $history->course_remaining,
            'approve_id' => auth()->user()->id,
            'approve_name' => auth()->user()->name,

        ]);


        $logHistory->update([
            'level_id_new' => $request->level_id,
            'level_name_new' => $request->level_name,
            'term_new' => $request->term,
            'bookuse_new' => $request->bookuse,
            'date_new' => $request->date,
            'stime_new' => $request->stime,
            'etime_new' => $request->etime,
            'comment_new' => $request->comment,
            'course_remaining_new' => $request->course_remaining,
            'approve_id' => auth()->user()->id,
            'approve_name' => auth()->user()->name,

        ]);

        $input['signature'] = $sign_name;
        $history->update($input);

        $filesb = $request->get('img');

        if ($filesb !== null) {
            $filebs = "";
            foreach ($filesb as $fileb) {
                //echo $fileb;

                $oldfile = FileUpload::where('oldname', $fileb)->get();
                $filebs  = $oldfile[0]->filename;
                //File::move(public_path() . '/file_upload/' . $oldfile[0]->filename, public_path() . '/file_upload/' . $filebs);
                //FileUpload::where('filename', $oldfile[0]->filename)->delete();

                HistoryStudentImg::create([

                    'history_id' => $history->id,
                    'student_id' => $history->student_id,
                    'img' => $filebs,

                ]);
            }
        }


        // dd($input,$history,$logHistory);

        return ['success' => 'Updated history successfully'];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy_all(Request $request)
    {
        //
        $his_id = $request->get('table_records');

        $loghistories = Histrories::whereIn('id', $his_id)->get();
        $histories = Histrories::whereIn('id', $his_id);

        foreach ($loghistories as $history) {

            $filePath = public_path('file_upload/' . $history->signature);

            // Delete the file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $dataimg = HistoryStudentImg::where([
                'history_id' => $history->id,
            ])->get();

            // Delete the found records
            if (!$dataimg->isEmpty()) {
                $dataimg->each(function ($item) {
                    $filename = $item->img;

                    // Build the full path to the file within the 'public/file_store' directory
                    $filePath = public_path('file_upload/' . $filename);

                    // Delete the file if it exists
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $item->delete();
                });
            }

            LogHistory::create([
                'history_id' => $history->id,
                'centre' => $history->centre,
                'student_id' => $history->student_id,
                'status' => 'delete',
                'level_id_old' => $history->level_id,
                'level_name_old' => $history->level_name,
                'term_old' => $history->term,
                'bookuse_old' => $history->bookuse,
                'date_old' => $history->date,
                'stime_old' => $history->stime,
                'etime_old' => $history->etime,
                'comment_old' => $history->comment,
                'course_remaining_old' => $history->course_remaining,
                'approve_id' => auth()->user()->id,
                'approve_name' => auth()->user()->name,
            ]);
        }
        if ($histories) {

            $histories->delete();
            return redirect()->route('histories.index')->with('success', 'Deleted Histrories to select successfully');

            return ['success' => true, 'message' => 'Deleted Histrories successfully'];
        } else {
            return ['success' => false, 'message' => 'Histrories not found'];
        }
    }
    public function destroy(Request $request)
    {
        //
        $id = $request->get('id');
        $history = Histrories::find($id);

        $filePath = public_path('file_upload/' . $history->signature);

        // Delete the file if it exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $dataimg = HistoryStudentImg::where([
            'history_id' => $id,
        ])->get();

        // Delete the found records
        if (!$dataimg->isEmpty()) {
            $dataimg->each(function ($item) {
                $filename = $item->img;

                // Build the full path to the file within the 'public/file_store' directory
                $filePath = public_path('file_upload/' . $filename);

                // Delete the file if it exists
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $item->delete();
            });
        }


        $logHistory = LogHistory::create([
            'history_id' => $history->id,
            'centre' => $history->centre,
            'student_id' => $history->student_id,
            'status' => 'delete',
            'level_id_old' => $history->level_id,
            'level_name_old' => $history->level_name,
            'term_old' => $history->term,
            'bookuse_old' => $history->bookuse,
            'date_old' => $history->date,
            'stime_old' => $history->stime,
            'etime_old' => $history->etime,
            'comment_old' => $history->comment,
            'course_remaining_old' => $history->course_remaining,
            'approve_id' => auth()->user()->id,
            'approve_name' => auth()->user()->name,
        ]);


        if ($history) {
            $history->delete();
            return ['success' => true, 'message' => 'Deleted Histrories successfully'];
        } else {
            return ['success' => false, 'message' => 'Histrories not found'];
        }
    }
}
