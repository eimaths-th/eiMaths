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
use App\Models\OrderDetailList;
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
use Illuminate\Support\Facades\DB;

class HistoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function formatDate($date)
    {
        return date('d-m-Y', strtotime($date));
    }

    public function sformatDate($date)
    {
        return date('Y-m-d', strtotime($date));
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


    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $datass = Histrories::orderByRaw("CONCAT(date, ' ', stime) DESC");
    //         if (!Gate::allows('all-centre')) {
    //             $datass->where('centre', Auth::user()->department->id);
    //         }

    //         if (!empty($request->get('sdate'))) {
    //             $dateRange = $request->input('sdate');
    //             if ($dateRange) {
    //                 $dateRangeArray = explode(' - ', $dateRange);

    //                 if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
    //                     $startDate = $dateRangeArray[0];
    //                     $endDate = $dateRangeArray[1];
    //                     $datass->whereBetween('date', [$startDate, $endDate]);
    //                 }
    //             }
    //             // dd($request->all());
    //         }

    //         $datas = $datass->get();
    //         return datatables()->of($datas)
    //             ->editColumn('checkbox', function ($row) {
    //                 return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
    //             })
    //             ->editColumn('centre', function ($row) {
    //                 // $code= 1;
    //                 if ($row->department) {

    //                     $centre = $row->department->name;
    //                 } else {
    //                     $centre = 'N/A';
    //                 }
    //                 return $centre;
    //             })
    //             ->editColumn('bookuse', function ($row) {
    //                 /* if ($row->booku) {
    //                     $bookuse = $row->booku->name;
    //                 } else { */
    //                 $bookuse = $row->bookuse;
    //                 //}
    //                 return $bookuse;
    //             })
    //             ->editColumn('code', function ($row) {
    //                 // $code= 1;
    //                 if ($row->student) {

    //                     $code = $row->student->code;
    //                 } else {
    //                     $code = 'N/A';
    //                 }
    //                 return $code;
    //             })
    //             ->editColumn('date', function ($row) {
    //                 //$code = $this->formatDate($row->date);
    //                 $code = $row->date;
    //                 return $code;
    //             })
    //             ->addColumn('setime', function ($row) {
    //                 //$code = $this->formatDate($row->date);
    //                 $code = $row->stime . ' - ' . $row->etime;
    //                 return $code;
    //             })
    //             ->editColumn('start_date', function ($row) {
    //                 //$code = $this->checkStartEndDate($row->student_id, $row->term, $row->level_id);
    //                 $code = $row->start_date . ' - ' . $row->end_date;
    //                 return $code;
    //             })
    //             ->editColumn('name', function ($row) {
    //                 // $name = 1;
    //                 $nic = $row->contact_nickname !== null && $row->contact_nickname !== '' ? '(' . $row->contact_nickname . ')' : '';
    //                 if ($row->student) {
    //                     $name = $row->student->name;
    //                 } else {
    //                     $name = 'N/A';
    //                 }
    //                 return $name . ' ' . $nic;
    //             })
    //             ->addColumn('more', function ($row) {
    //                 return '';
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $html = '';
    //                 if (Gate::allows('student-edit')) {
    //                     $html = '<button type="button" class="btn btn-sm btn-primary btn-view" id="getViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View </button> ';
    //                     $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit </button> ';
    //                     // $html .= '<button type="button" class="btn btn-sm btn-info btn-edit" id="getLogData" data-id="' . $row->id . '"><i class="fa fa-clipboard"></i> Log </button> ';
    //                 } else {
    //                     $html = '<button type="button" class="btn btn-sm btn-primary disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> View</button> ';
    //                     $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
    //                 }
    //                 if (Gate::allows('view-log')) {

    //                     $html .= '<button type="button" class="btn btn-sm btn-info btn-edit" id="getLogData" data-id="' . $row->id . '"><i class="fa fa-clipboard"></i> Log </button> ';
    //                 } else {

    //                     $html .= '<button type="button" class="btn btn-sm btn-info btn-edit" id="getLogData" disabled><i class="fa fa-clipboard"></i> Log </button> ';
    //                 }

    //                 // if (Gate::allows('student-edit')) {
    //                 //     $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
    //                 // } else {
    //                 //     $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
    //                 // }

    //                 if (Gate::allows('student-delete')) {
    //                     $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
    //                 } else {
    //                     $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
    //                 }
    //                 return $html;
    //             })->rawColumns(['checkbox', 'action', 'more'])->toJson();
    //     }

    //     $centre = Department::where([['status', '1']])
    //         ->orderBy("name", "asc")->get();
    //     $term = term::where([['status', '1']])
    //         ->orderBy("name", "asc")->get();
    //     $sterm = Sterm::where([['status', '1']])
    //         ->orderBy("name", "asc")->get();
    //     $bookuse = bookuse::where([['status', '1']/* , ['type', '1'] */])
    //         ->orderBy("name", "asc")->get();
    //     $level = level::where([['status', '1']])
    //         ->orderBy("name", "asc")->get();

    //     $role = 'Teacher';
    //     $teacher = User::whereHas('roles', function ($query) use ($role) {
    //         $query->where('name', $role);
    //     })->get();

    //     // dd($last_book,$last_level,$graduated);
    //     // $histories = Histrories::all();

    //     return view('histories.index', [
    //         'centre' => $centre,
    //         'bookuse' => $bookuse,
    //         'term' => $term,
    //         'sterm' => $sterm,
    //         // 'histories' => $histories,
    //         'level' => $level,
    //         'lteacher' => $teacher
    //     ]);
    // }
    public function index(Request $request)
    {
        if ($request->ajax()) {

            // ✅ ใช้ with() preload ความสัมพันธ์ เพื่อลดจำนวน query
            $datass = Histrories::with(['student', 'department']);

            // ✅ จำกัดศูนย์ตามสิทธิ์
            if (!Gate::allows('all-centre')) {
                $datass->where('centre', Auth::user()->department->id);
            }

            // ✅ กรองตามวันที่
            if (!empty($request->get('sdate'))) {
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);
                    if (count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $datass->whereBetween('date', [$startDate, $endDate]);
                    }
                }
            }

            // ✅ ใช้ eloquent() ไม่ใช่ get()
            return datatables()
                ->eloquent($datass)
                // Ensure server-side ordering works for these columns
                ->orderColumn('date', function($query, $order) {
                    $query->orderBy('date', $order)->orderBy('stime', $order);
                })
                ->orderColumn('level_name', 'level_name $1')
                ->orderColumn('term', 'term $1')
                // Custom filters for related columns to avoid querying non-existent histories.* fields
                ->filterColumn('code', function ($query, $keyword) {
                    $query->whereHas('student', function ($q) use ($keyword) {
                        $q->where('code', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->whereHas('student', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('centre', function ($row) {
                    return $row->department ? $row->department->name : 'N/A';
                })
                ->editColumn('bookuse', function ($row) {
                    return $row->bookuse ?? 'N/A';
                })
                ->editColumn('code', function ($row) {
                    return $row->student ? $row->student->code : 'N/A';
                })
                ->editColumn('date', function ($row) {
                    return $row->date;
                })
                ->addColumn('setime', function ($row) {
                    return $row->stime . ' - ' . $row->etime;
                })
                ->editColumn('start_date', function ($row) {
                    return $row->start_date . ' - ' . $row->end_date;
                })
                ->editColumn('name', function ($row) {
                    $nic = $row->contact_nickname ? '(' . $row->contact_nickname . ')' : '';
                    return ($row->student ? $row->student->name : 'N/A') . ' ' . $nic;
                })
                ->addColumn('more', function () {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    if (Gate::allows('student-edit')) {
                        $html .= '<button type="button" class="btn btn-sm btn-primary btn-view" id="getViewData" data-id="' . $row->id . '"><i class="fa fa-eye"></i> View</button> ';
                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-primary disabled" title="No Permission"><i class="fa fa-eye"></i> View</button> ';
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" title="No Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }

                    if (Gate::allows('view-log')) {
                        $html .= '<button type="button" class="btn btn-sm btn-info btn-edit" id="getLogData" data-id="' . $row->id . '"><i class="fa fa-clipboard"></i> Log</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-info disabled"><i class="fa fa-clipboard"></i> Log</button> ';
                    }

                    if (Gate::allows('student-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" title="No Permission"><i class="fa fa-trash"></i> Delete</button>';
                    }

                    return $html;
                })
                ->rawColumns(['checkbox', 'action', 'more'])
                ->make(true);
        }

        // ✅ โหลดข้อมูลประกอบสำหรับ view ปกติ
        $centre = Department::where('status', '1')->orderBy('name', 'asc')->get();
        $term = term::where('status', '1')->orderBy('name', 'asc')->get();
        $sterm = Sterm::where('status', '1')->orderBy('name', 'asc')->get();
        $bookuse = bookuse::where('status', '1')->orderBy('name', 'asc')->get();
        $level = level::where('status', '1')->orderBy('name', 'asc')->get();

        $teacher = User::whereHas('roles', function ($q) {
            $q->where('name', 'Teacher');
        })->get();

        return view('histories.index', [
            'centre' => $centre,
            'bookuse' => $bookuse,
            'term' => $term,
            'sterm' => $sterm,
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
        //$student = Contact::find($std_id);
        $order = Order::where('cid', $std_id)->get();
        $order_detail = OrderDetailList::select('level', DB::raw('(SELECT id FROM levels WHERE levels.name = order_detail_lists.level) as level_id'))
            ->whereIn('order_id', $order->pluck('id'))
            ->groupBy('level')->get();
        /*  $lv1 = $student->level;
        $lv2 = $student->level2;
        $levels = level::whereBetween('id', [$lv1, $lv2])->get(); */
        foreach ($order_detail as $level) {
            $select_list .= '<option value="' . $level->level_id . '" >' . $level->level . '</option>';
        }
        if ($type == 'add') {

            return response()->json([
                'html' =>  $select_list
            ]);
        } else {
            return $select_list;
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

    public function get_product_id($std_id)
    {
        // Retrieve orders for the given student ID
        $orders = Order::where('cid', $std_id)->orderBy('id', 'asc')->get();

        // Extract order IDs from the orders
        $orderIds = $orders->pluck('id');

        // Retrieve order details and concatenate product IDs
        $orderDetails = OrderDetail::whereIn('order_id', $orderIds)
            ->selectRaw('order_id, GROUP_CONCAT(product_id) as product_ids')
            ->groupBy('order_id')
            ->get();

        // Initialize an empty array to store book uses
        $bookuses = [];

        // Loop through each order detail to extract product IDs and retrieve book uses
        foreach ($orderDetails as $orderDetail) {
            // Explode the concatenated product IDs string
            $productIdsArray = explode(',', $orderDetail->product_ids);

            // Ensure $productIdsArray contains at least 2 elements before accessing
            if (count($productIdsArray) >= 2) {
                // Retrieve book uses where the id falls between the range specified in $productIdsArray
                $bookuses[] = BookUse::whereBetween('id', [$productIdsArray[0], $productIdsArray[1]])->get()->toArray();
            }
        }

        // Flatten the array of book uses and convert each element to object
        $bookuses = collect(array_merge(...$bookuses))->map(function ($item) {
            return (object) $item;
        });

        //dd($bookuses);

        return $bookuses;
    }

    public function reorder_remain($std_id, $term, $level_id)
    {
        $histories = Histrories::where([
            ['student_id', $std_id],
            ['term', $term],
            ['level_id', $level_id]
        ])->orderByRaw("CONCAT(date, ' ', stime) ASC")->get();

        //$contact = Contact::where('id', $std_id)->first();
        //$orderlist = OrderDetailList::where('order_id', $contact->order)->get();

        $orders = Order::where('cid', $std_id)->orderBy('id', 'asc')->get();
        $orderIds = $orders->pluck('id');

        $orderlist = OrderDetailList::whereIn('order_id', $orderIds)
            ->get();



        $book_id = null;
        $matchingBookUses = [];
        foreach ($this->get_product_id($std_id) as $bookuse) {
            if ($bookuse->term_id == $term && $bookuse->level_id == $level_id) {

                $matchingBookUses[$bookuse->id] = $bookuse->qty;
                //break;
                foreach ($orderlist as $order) {
                    if ($order->book == $bookuse->name) {
                        $book_id = $bookuse->id;
                    }
                }
            }
        }

        //dd($book_id);
        if ($book_id === null) {
            exit();
        } else {
            //last record term
            $lasthistoriesCount = Histrories::where([
                ['student_id', $std_id],
                ['level_id', $level_id],
                ['term', '<', $term],
            ])->orderByRaw("CONCAT(date, ' ', stime) ASC")->count();

            //dd($matchingBookUses);

            if ($matchingBookUses[$book_id] < 10 && $lasthistoriesCount !== 0) {
                $count = 4;
            } else {
                $count = 9;
            }

            foreach ($histories as $history) {
                // Update course_remaining
                $history->update(['course_remaining' => $count]);

                // Decrement count for next record
                $count--;

                // Ensure count doesn't go below 0
                if ($count < 0) {
                    $count = 0;
                }
            }
        }
    }

    public function checkRemaining($id)
    {
        // $id = 103;
        $student = Contact::find($id);
        $studentStartLevel = $student->level;
        $studentStartTerm = $student->term;
        $studentEndLevel = $student->level2;
        $studentEndTerm = $student->term2;

        $coin = 0;
        $historyCount = 0;

        if ($studentEndLevel && $studentEndTerm) {
            for ($i = $studentStartLevel; $i <= $studentEndLevel; $i++) {

                if ($i == $studentStartLevel) {
                    $sterm = $studentStartTerm;
                } else {
                    $sterm = 1;
                }

                if ($i == $studentEndLevel) {
                    $eterm = $studentEndTerm;
                } else {
                    $eterm = 4;
                }

                for ($j = $sterm; $j <= $eterm; $j++) {
                    $history = Histrories::where([
                        ['student_id', $id],
                        ['level_id', $i],
                        ['term', $j],
                    ])->get();

                    if ($history->count() > 0) {

                        $historyCount += $history->count();
                    }

                    $coin++;
                }
            }
        }

        // $history = Histrories::where([
        //     ['student_id', $student->id],
        //     ['level_id', $studentStartLevel],
        //     ['term', $studentStartTerm],
        // ])->first();

        // if ($history) {
        //     $history->whereBetween('date', [$history->start_date, $history->end_date])->get();
        // }

        $scoin = $coin * 10;

        // $remain = $scoin - $historyCount;

        // $text = $remain . ' / ' . $scoin . ' เรียนไปแล้ว : ' . $historyCount . ' ครั้ง';

        return $coin;
    }

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
        $coin = $this->checkRemaining($std_id);

        $history = Histrories::where([
            ['student_id', $std_id],
            ['term', $term],
            ['level_id', $level_id]
        ])->get();


        //$contact = Contact::where('id', $std_id)->first();
        //$orderlist = OrderDetailList::where('order_id', $contact->order)->get();
        $orders = Order::where('cid', $std_id)->orderBy('id', 'asc')->get();
        $orderIds = $orders->pluck('id');

        $orderlist = OrderDetailList::whereIn('order_id', $orderIds)
            ->get();

        $book_id = null;
        $qty = null;
        $matchingBookUses = [];
        foreach ($this->get_product_id($std_id) as $bookused) {
            if ($bookused->term_id == $term && $bookused->level_id == $level_id) {

                $matchingBookUses[$bookused->id] = $bookused->qty;
                //break;
                foreach ($orderlist as $order) {
                    if ($order->book == $bookused->name) {
                        $book_id = $bookused->id;
                        $qty = $bookused->qty;
                    }
                }
            }
        }



        //term qty
        /* foreach ($this->get_product_id($std_id) as $bookused) {
            if ($bookused->term_id == $term && $bookused->level_id == $level_id) {
                $qty = $bookused->qty;
                //break;
            }
        } */

        if ($qty === null) {
            return response()->json(['success' => false, 'message' => 'นักเรียนไม่ได้ลงเรียน Level หรือ Term นี้ ']);
        }

        //dd( $history->count(),$std_id,$term,$level_id,$history,$qty);
        if ($history->isEmpty()) {
            $start_date = Carbon::parse($request->input('date'));
            $end_date = $start_date->copy()->addMonths(4 * $coin);
            $course_remaining = 9;
        } elseif ($history->count() < $qty) {
            $lastHistory = $history->sortByDesc('date')->first();
            $firstHistory = $history->sortBy('date')->first();
            $course_remaining = $lastHistory->course_remaining - 1;
            //$course_remaining = 9 - $history->count();
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

        //reorder remain
        $this->reorder_remain($std_id, $term, $level_id);


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

        //start-end
        $ste = $this->checkStartEndDate($history->student_id, $history->term, $history->level_id);
        $stea = explode(' - ', $ste);

        // Update start_date and end_date
        $history->start_date = $this->sformatDate($stea[0]); // Assuming start date is the first element of $stea
        $history->end_date = $this->sformatDate($stea[1]);   // Assuming end date is the second element of $stea

        // Save changes
        $history->save();

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

        //start-end
        $ste = $this->checkStartEndDate($history->student_id, $history->term, $history->level_id);
        $stea = explode(' - ', $ste);

        // Update start_date and end_date
        $history->start_date = $this->sformatDate($stea[0]); // Assuming start date is the first element of $stea
        $history->end_date = $this->sformatDate($stea[1]);   // Assuming end date is the second element of $stea

        // Save changes
        $history->save();

        $teacher = User::where('id', $history->teacher_id)->first();
        if ($teacher !== null) {
            $teacher_name = $teacher->name;
            $teacher_id = $teacher->id;
        } else {
            $teacher_name = 'ไม่มีการบันทึกครูผู้สอน';
            $teacher_id = '';
        }

        $level_list = $this->stdfind('edit', $history->student_id);

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
            'level' => $level_list,
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
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Start Date : </strong>
                                <span class="text-danger" for="" >' . $historys->start_date_old . '</span>
                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> End Date : </strong>
                                <span class="text-danger" for="" >' . $historys->end_date_old . '</span>
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
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Start Date : </strong>
                                <span class="text-success" for="" >' . $historys->start_date_new . '</span>
                            </div>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> End Date : </strong>
                                <span class="text-success" for="" >' . $historys->end_date_new . '</span>
                            </div>
                        </div>
                    </div>

                    <label for="" class="breadcrumb float-sm-right"> Approve By : ' . $historys->approve_name . '</label> <br>

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
        // dd($input,$history);

        $validator = Validator::make($request->all(), [
            'level_id' => 'required',
            'teacher_id' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'date' => 'required',
            'stime' => 'required',
            'etime' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
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
            'start_date.required' => 'Please provide start date.',
            'end_date.required' => 'Please provide end date.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }



        //$contact = Contact::where('id', $history->student_id)->first();

        $orders = Order::where('cid', $history->student_id)->orderBy('id', 'asc')->get();
        $orderIds = $orders->pluck('id');

        $orderlist = OrderDetailList::whereIn('order_id', $orderIds)
            ->get();

        //$orderlist = OrderDetailList::where('order_id', $contact->order)->get();

        $book_id = null;
        $qty = null;
        $matchingBookUses = [];
        foreach ($this->get_product_id($history->student_id) as $bookused) {
            if ($bookused->term_id == $request->term && $bookused->level_id == $request->level_id) {

                $matchingBookUses[$bookused->id] = $bookused->qty;
                //break;
                foreach ($orderlist as $order) {
                    if ($order->book == $bookused->name) {
                        $book_id = $bookused->id;
                        $qty = $bookused->qty;
                    }
                }
            }
        }
        //term qty
        /*  foreach ($this->get_product_id($history->student_id) as $bookused) {
            if ($bookused->term_id == $request->term && $bookused->level_id == $request->level_id) {
                $qty = $bookused->qty;
                //break;
            }
        }
 */
        if ($qty === null) {
            return ['success' => 'นักเรียนไม่ได้ลงเรียน Level หรือ Term นี้ '];
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
            'start_date_old' => $history->start_date,
            'end_date_old' => $history->end_date,
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
            'etime_new' => $request->etime,
            'start_date_new' => $request->start_date,
            'end_date_new' => $request->end_date,
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


        //reorder remain
        $this->reorder_remain($history->student_id, $request->term, $request->level_id);
        // dd($input,$history,$logHistory);

        return ['success' => 'Updated history successfully'];
    }


    public function destroy_func($id)
    {
        $history = Histrories::find($id);
        if (!$history) {
            return false; // Return false if history not found
        }

        $stu_id = $history->student_id;
        $stu_term = $history->term;
        $stu_level = $history->level_id;

        $filePath = public_path('file_upload/' . $history->signature);

        // Delete the file if it exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $dataimg = HistoryStudentImg::where('history_id', $id)->get();

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

        // Create log history
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

        // Delete the history
        $history->delete();

        // Reorder remaining histories
        $this->reorder_remain($stu_id, $stu_term, $stu_level);

        return true;
    }

    public function destroy_all(Request $request)
    {
        $his_id = $request->get('table_records');

        foreach ($his_id as $id) {
            $this->destroy_func($id);
        }

        return redirect()->route('histories.index')->with('success', 'Deleted Histories successfully');
    }

    public function destroy(Request $request)
    {
        $id = $request->get('id');
        $de = $this->destroy_func($id);

        if ($de) {
            return ['success' => true, 'message' => 'Deleted History successfully'];
        } else {
            return ['success' => false, 'message' => 'History not found'];
        }
    }
}
