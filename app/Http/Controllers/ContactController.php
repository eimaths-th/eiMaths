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
use App\Models\Sterm;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\Histrories;
use App\Models\OrderDetail;
use App\Models\OrderDetailList;
use App\Models\OrderRunningNumber;
use Illuminate\Support\Facades\Gate;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportForm;
use App\Exports\ExportExcel;
use App\Models\CoursePending;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

use Carbon\Carbon;

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
        $this->middleware('permission:student-list|student-create|student-edit|student-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:student-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:student-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:student-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */

    public function formatDate($date)
    {
        return date('d-m-Y', strtotime($date));
    }

    public function checkEndDate($id)
    {
        $data = Histrories::where('student_id', $id)
            ->orderBy('id', 'DESC')
            ->first();
        if ($data) {
            $history = Histrories::where([
                ['student_id', $data->student_id],
                /* ['term', $data->term], */
                /* ['level_id', $data->level_id] */
            ])->get();
            // dd( $history->count(),$std_id,$term,$level_id,$history,);
            //$lastHistory = $history->sortByDesc('created_at')->first();

            ///$firstHistory = $history->sortBy('date')->first();
            $firstHistory = $history->sortByDesc('date')->first();

            //dd($firstHistory);
            $now = Carbon::now();

            if ($firstHistory) {
                $start_date = $firstHistory->start_date;
                $end_date = $firstHistory->end_date;
                // $end_date = Carbon::parse($end_date)->format('Y-m-d');
                $startDateFormat = $this->formatDate($start_date);
                $endDateFormat = $this->formatDate($end_date);
                $end_date = Carbon::parse($end_date);

                // ตรวจสอบว่า $now มากกว่า $end_date + 3 เดือน หรือไม่
                if ($now->greaterThan($end_date->copy()->addMonths(3))) {
                    return '-';
                }

                return $startDateFormat . ' - ' . $endDateFormat;
            } else {
                return '-';
            }
        } else {
            return '-';
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

        $free_course = $student->free_course;

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

        $scoin = ($coin * 10) + $free_course;

        $remain = $scoin - $historyCount;

        $text = ' เรียนไปแล้ว : ' . $historyCount . ' ฟรี ' . $free_course . ' เหลือ ' . $remain . ' / ' . $scoin;

        return $text;
    }

    public function maxHistory($id)
    {
        // $id = 103;
        $student = Contact::find($id);
        $studentStartLevel = $student->level;
        $studentStartTerm = $student->term;
        $studentEndLevel = $student->level2;
        $studentEndTerm = $student->term2;

        $free_course = $student->free_course;

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

        $scoin = ($coin * 10) + $free_course;

        $remain = $scoin - $historyCount;


        return $remain;
    }


    public function index(Request $request)
    {
        //  dd($request->all());

        if ($request->ajax()) {
            //sleep(2);
            $query = Contact::select(
                "contacts.*",
                "departments.name as centre_name"
            )
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("levels", "contacts.level", "=", "levels.id")
                ->join("bookuses", "contacts.bookuse", "=", "bookuses.id")
                ->join("terms as pterms", "contacts.term", "=", "pterms.id")
                ->where('discontinued', '0');

            $centre = $request->get('centre');
            if ($centre) {
                $query->where('contacts.centre', $centre);
            }

            if (!Gate::allows('all-centre')) {
                $query->where('centre', Auth::user()->department->id);
            }

            if (!empty($request->get('sdate'))) {
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('contacts.start_date', [$startDate, $endDate]);
                    }
                }
            }
            $query->where("contacts.type", "student");
            $query->orderBy("contacts.id", "desc");

            //$sqlQuery = $query->toSql();
            //dd($sqlQuery);
            $datas = $query->get();

            //dd($datas);
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('level_name', function ($row) {
                    if (!empty($row->level2_name)) {
                        return $row->level_name . ' - ' . $row->level2_name;
                    } else {
                        return $row->level_name;
                    }
                })
                ->editColumn('term', function ($row) {
                    if (!empty($row->term2)) {
                        return $row->term . ' - ' . $row->term2;
                    } else {
                        return $row->term;
                    }
                })
                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    $nickname = $row->nickname ?? '-';
                    return $name . ' (' . $nickname . ')';
                })
                ->editColumn('ref', function ($row) {
                    return $row->ref !== null && $row->ref !== '' ? $row->ref : '-';
                })
                ->editColumn('start_date', function ($row) {
                    return $this->formatDate($row->start_date);
                })
                ->addColumn('end_date', function ($row) {
                    return $this->checkEndDate($row->id);
                })
                ->addColumn('remaining', function ($row) {
                    return $this->checkRemaining($row->id);
                })
                ->editColumn('bookuse_name', function ($row) {
                    if (!empty($row->bookuse2_name)) {
                        return ' ( ' . $row->bookuse_name . ') - (' . $row->bookuse2_name . ')';
                    } else {
                        return $row->bookuse_name;
                    }
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    // if (Gate::allows('student-edit')) {
                    //     $html .= '<button type="button" class="btn btn-sm btn-primary btn-fastHistory" id="fastHistory" data-id="' . $row->id . '"><i class="fa fa-history"></i> Fast History</button> ';
                    // } else {
                    //     $html .= '<button type="button" class="btn btn-sm btn-primary disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-history"></i> Fast History</button> ';
                    // }

                    if (Gate::allows('student-edit')) {
                        $html .= '<button type="button" class="btn btn-sm btn-info btn-show" id="getCourseData" data-id="' . $row->id . '"><i class="fa fa-search"></i>View Course</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-info disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-search"></i>  View Course</button> ';
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
            /* ->filter(function ($instance) use ($request) {
                    if ($request->get('approved') == '0' || $request->get('approved') == '1') {
                        $instance->where('contacts.code', $request->get('approved'));
                    }
                    if (!empty($request->get('search'))) {
                        $instance->where(function ($w) use ($request) {
                            $search = $request->get('search');
                            $w->orWhere('name', 'LIKE', "%$search%")
                                ->orWhere('email', 'LIKE', "%$search%");
                        });
                    }
                }) */
        }

        $centre = Department::where([['status', '1']])->orderBy("name", "asc")->get();
        $term = term::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $sterm = Sterm::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $bookuse = bookuse::where([['status', '1']/* , ['type', '1'] */])
            ->orderBy("name", "asc")->get();
        $level = level::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        //dd($term);

        return view('contacts.index')->with(
            ['centre' => $centre]
        )
            ->with(['bookuse' => $bookuse])
            ->with(['level' => $level])
            ->with(['term' => $term])
            ->with(['sterm' => $sterm]);
    }

    public function graduated(Request $request)
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
                ->editColumn('nickname', function ($row) {
                    return $row->nickname !== null && $row->nickname !== '' ? $row->nickname : '-';
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

        return view('graduated.index', [
            'centre' => $centre,
            'bookuse' => $bookuse,
            'term' => $term,
            'sterm' => $sterm,
            'level' => $level
        ]);
    }


    public function get_course($std_id)
    {
        $orders = Order::where('cid', $std_id)->orderBy('id', 'asc')->get();
        $orderIds = $orders->pluck('id');

        $orderDetails = OrderDetailList::whereIn('order_id', $orderIds)
            ->get();

        $html = '';
        $i = 1;

        foreach ($orderDetails as $orderDetail) {
            $html .= '<tr>';
            $html .= '<td>' . $i . '</td>';
            $html .= '<td>' . $orderDetail->level . '</td>';
            $html .= '<td>' . $orderDetail->term . '</td>';
            $html .= '<td><span class="tag tag-success">' . $orderDetail->book . '</span></td>';
            $html .= '<td>' . $orderDetail->order->free_course . '</td>';
            $html .= '<td>' . $orderDetail->order->free_course_reason . '</td>';
            $html .= '<td>' . $orderDetail->created_at . '</td>';
            $html .= '</tr>';
            $i++;
        }

        return $html;
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($centre)
    {
        if ($centre == 0) {
            $centre = Auth::user()->department->id;
        }
        $rnumber = studentRunningNumber::pre_generate($centre);
        return response()->json([
            'running' =>  $rnumber
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function demo_list(Request $request)
    {

        if ($request->ajax()) {
            $query = Contact::where('type', 'demo');

            if (!empty($request->get('sdate'))) {
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('start_date', [$startDate, $endDate]);
                    }
                }
            }
            $query->orderBy("id", "desc");

            $datas = $query->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('level_name', function ($row) {
                    if (!empty($row->level2_name)) {
                        return $row->level_name . ' - ' . $row->level2_name;
                    } else {
                        return $row->level_name;
                    }
                })
                ->editColumn('nickname', function ($row) {
                    return $row->nickname !== null && $row->nickname !== '' ? $row->nickname : '-';
                })
                ->editColumn('centre_name', function ($row) {
                    return '-';
                })
                ->editColumn('start_date', function ($row) {
                    return $this->formatDate($row->start_date);
                })
                ->addColumn('end_date', function ($row) {
                    return $this->checkEndDate($row->id);
                })
                ->editColumn('bookuse_name', function ($row) {
                    if (!empty($row->bookuse2_name)) {
                        return ' ( ' . $row->bookuse_name . ') - (' . $row->bookuse2_name . ')';
                    } else {
                        return $row->bookuse_name;
                    }
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';


                    if (Gate::allows('student-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action', 'more'])->toJson();
            /* ->filter(function ($instance) use ($request) {
                    if ($request->get('approved') == '0' || $request->get('approved') == '1') {
                        $instance->where('contacts.code', $request->get('approved'));
                    }
                    if (!empty($request->get('search'))) {
                        $instance->where(function ($w) use ($request) {
                            $search = $request->get('search');
                            $w->orWhere('name', 'LIKE', "%$search%")
                                ->orWhere('email', 'LIKE', "%$search%");
                        });
                    }
                }) */
        }
        $centre =  (object) ['id' => 1, 'name' => 'demo'];
        $term = ['demo'];
        $sterm = ['demo'];
        $bookuse = ['demo'];
        $level = ['demo'];
        $centre1 = Department::where([['status', '1']])->orderBy("name", "asc")->get();

        // dd(
        //     $centre,
        //     $centre->id,
        //     $centre1,
        //     $term,
        //     $sterm,
        //     $bookuse,
        //     $level,
        // );

        return view('contacts.demo_index')->with(
            ['centre' => $centre]
        )
            ->with(['bookuse' => $bookuse])
            ->with(['level' => $level])
            ->with(['term' => $term])
            ->with(['sterm' => $sterm]);
    }

    public function demo_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255',
            'school' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'password' => 'required|min:6|confirmed',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $demo = Contact::create([
            'code' => $request->code,
            'school' => $request->school,
            'name' => $request->name,
            'nickname' => $request->nickname,
            'start_date' => date('Y-m-d'),
            'password' =>  bcrypt($request->password),
            'type' =>  'demo',
        ]);

        // Return a success response
        return response()->json(['success' => 'Demo user created successfully!']);
    }

    public function demo_code()
    {
        $latestContact = Contact::where('type', 'demo')->latest('id')->first();

        if (is_null($latestContact)) {
            $latestCodeNumber = 0;
        } else {
            $latestCodeNumber = (int) str_replace('DEMO', '', $latestContact->code);
        }

        // Increment the code and format it as 'DEMOXXXX' (e.g., 'DEMO0002')
        $newDemoCode = 'DEMO' . str_pad($latestCodeNumber + 1, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'demoCode' => $newDemoCode,
        ]);
    }
    public function thank_you()
    {
        return view('home.thankq');
    }

    public function store(Request $request)
    {

        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:contacts',
            'start_date' => 'required|string|max:10',
            //'start_term' => 'required',
            'school' => 'required|string|max:255',
            'level' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'father_name' => 'nullable|string|max:255|required_without:mother_name',
            //'father_email' => 'nullable|string|max:255|required_with:father_name,father_mobile',
            'father_mobile' => 'nullable|string|max:20|required_with:father_name',
            'mother_name' => 'nullable|string|max:255|required_without:father_name',
            //'mother_email' => 'nullable|string|max:255|required_with:mother_name,mother_mobile',
            'mother_mobile' => 'nullable|string|max:20|required_with:mother_name',
        ], [
            'name.required' => 'Student name must not be empty.',
            'name.unique' => 'This student name already exists in the database.',
            'start_date.required' => 'Please provide a start date.',
            'start_term.required' => 'Please select a start term.',
            'school.required' => 'Please provide the school name.',
            'level.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
            'father_name.required_without' => 'Either Father name or Mother name must be provided.',
            'father_email.required_with' => 'Father email is required when Father name is provided.',
            'father_mobile.required_with' => 'Father mobile is required when Father name is provided.',
            'mother_name.required_without' => 'Either Mother name or Father name must be provided.',
            'mother_email.required_with' => 'Mother email is required when Mother name is provided.',
            'mother_mobile.required_with' => 'Mother mobile is required when Mother name is provided.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $bookq = bookuse::find($request->get('bookuse'));
        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));

        if ($bookq->type == 1) {
            $mprice = $levelq->price;
            $mbprice = $levelq->book_price;
            $fprice = $levelq->price;
        } elseif ($bookq->type == 2) {
            $mprice = $levelq->half_price;
            $mbprice = $levelq->book_half_price;
            $fprice = $levelq->half_price;
        }

        if (!empty($request->get('level2'))) {

            $bookq2 = bookuse::find($request->get('bookuse2'));
            $levelq2 = Level::find($request->get('level2'));
            $termq2 = Term::find($request->get('term2'));

            $mprice = 0;
            $mbprice = 0;
            $coin = 0;
            for ($i = $levelq->id; $i <= $levelq2->id; $i++) {
                $sbook = 1;

                if ($i == $levelq->id) {
                    $sterm = $termq->name;
                } else {
                    $sterm = 1;
                }

                if ($i == $levelq2->id) {
                    $eterm = $termq2->name;
                } else {
                    $eterm = 4;
                }

                for ($j = $sterm; $j <= $eterm; $j++) {
                    if ($i == $levelq->id && $j == $sterm) {
                        $sbook = $bookq->type;
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $sbook = $bookq->type;
                    } else {
                        $sbook = 1;
                    }
                    //echo $j . "<br>";
                    /*  $books = bookuse::where('type', '=', $sbook)
                        ->where('level_id', '=', $i)
                        ->where('term_id', '=', $j)
                        ->get(); */
                    $books = bookuse::select(
                        "bookuses.*",
                        "bookuses.type as level_type",
                        "levels.name as level_name",
                        "levels.price as price",
                        "levels.half_price as half_price",
                        "levels.book_price as book_price",
                        "levels.book_half_price as book_half_price",
                        "terms.name as term_name",
                    )
                        ->join("terms", "terms.id", "=", "bookuses.term_id")
                        ->join("levels", "levels.id", "=", "bookuses.level_id");

                    if ($i == $levelq->id && $j == $sterm) {
                        $books->where('bookuses.id', '=', $bookq->id);
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $books->where('bookuses.id', '=', $bookq2->id);
                    } else {
                        $books->where('type', '=', $sbook)->where('bookuses.level_id', '=', $i)
                            ->where('bookuses.term_id', '=', $j);
                    }
                    $books = $books->get();
                    $bookss[] = $books;

                    if ($books[0]->level_type == 1) {
                        $mprice = $mprice + $books[0]->price;
                        $mbprice = $mbprice + $books[0]->book_price;
                    } else {
                        $mprice = $mprice + $books[0]->half_price;
                        $mbprice = $mbprice + $books[0]->book_half_price;
                    }

                    $coin++;
                }
            }


            if ($bookq2->type == 1) {
                $fprice2 = $levelq2->price;
            } elseif ($bookq2->type == 2) {
                $fprice2 = $levelq2->half_price;
            }
        } else {
            $books = bookuse::select(
                "bookuses.*",
                "bookuses.type as level_type",
                "levels.name as level_name",
                "levels.price as price",
                "levels.half_price as half_price",
                "levels.book_price as book_price",
                "levels.book_half_price as book_half_price",
                "terms.name as term_name",
            )
                ->join("terms", "terms.id", "=", "bookuses.term_id")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('bookuses.level_id', '=', $request->get('level'))
                ->where('bookuses.term_id', '=', $request->get('term'))
                ->where('bookuses.id', '=', $request->get('bookuse'))
                ->get();
            $bookss[] = $books;
        }

        // dd($coin);

        $input = $request->all();
        $input['code'] = studentRunningNumber::generate($request->get('centre'));
        $input['level_name'] = $levelq->name;
        $input['term_name'] = $termq->name;
        $input['bookuse_name'] = $bookq->name;
        $input['type'] = 'student';

        if (!empty($request->get('level2'))) {
            $input['level2_name'] = $levelq2->name;
            $input['term2_name'] = $termq2->name;
            $input['bookuse2_name'] = $bookq2->name;
        }

        $contact = Contact::create($input);
        // dd($request->all(), $input);

        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $contact->id;
        $input_order['order_number'] = OrderRunningNumber::generate($request->get('centre'));
        $input_order['total_price'] = $mprice + $mbprice;
        $order = Order::create($input_order);
        $input_order['free_course'] = $request->get('free_course', 0);
        $input_order['free_course_reason'] = $request->get('free_course_reason');
        $order->update($input_order);

        foreach ($bookss as $dresult) {
            foreach ($dresult as $book) {
                if ($book->level_type == 1) {
                    $tprice = $book->price;
                    $bprice = $book->book_price;
                } elseif ($book->level_type == 2) {
                    $tprice = $book->half_price;
                    $bprice = $book->book_half_price;
                }
                OrderDetailList::create([
                    'order_id' => $order->id,
                    'level' => $book->level_name,
                    'term' => $book->term_name,
                    'book' => $book->name,
                    'price' => $tprice,
                    'bprice' => $bprice,
                ]);
            }
        }

        $contact->order = $order->id;
        $contact->save();

        $input_order_detail['order_id'] = $order->id;
        $input_order_detail['product_id'] = $bookq->id;
        $input_order_detail['pname'] = $bookq->name;
        $input_order_detail['punit'] = $bookq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $fprice;
        $order_detail = OrderDetail::create($input_order_detail);

        if (!empty($request->get('level2'))) {
            $input_order_detail2['order_id'] = $order->id;
            $input_order_detail2['product_id'] = $bookq2->id;
            $input_order_detail2['pname'] = $bookq2->name;
            $input_order_detail2['punit'] = $bookq2->unit;
            $input_order_detail2['quantity'] = 1;
            $input_order_detail2['price'] = $fprice2;
            $order_detail2 = OrderDetail::create($input_order_detail2);
        }

        $select_list_contact = '<option value="' . $contact->id . '" > ' . $contact->name . '</option>';
        return response()->json([
            'success' => 'Added student information successfully.',
            'contact' => $select_list_contact,
            'cid' => $contact->id,
            'oid' => $order->id,
            'coin' => $coin,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */

    public function getRef($id)
    {

        $data = Contact::find($id);
        return response()->json([
            'ref' => $data->ref ?? '',
            // 'data' => $data,
        ]);
    }

    public function updateWaitingForPayment($status, $id)
    {

        $data = CoursePending::find($id);
        if ($status == 'wait') {
            $data->update(['status' => 3]);
        }
    }
    public function receipt(Request $request)
    {

        // dd($request->all());
        //
        $validator = Validator::make($request->all(), [
            'centre' => 'required',
            'student' => 'required',
            'level' => 'required',
            'term' => 'required',
            'bookuse' => 'required',
            'level2' => 'required',
            'term2' => 'required',
            'bookuse2' => 'required',
        ], [
            'centre.required' => 'Please select a centre.',
            'student.required' => 'Please select a student.',
            'level.required' => 'Please select a level.',
            'term.required' => 'Please select a term.',
            'bookuse.required' => 'Please specify book usage.',
            'level2.required' => 'Please select to level.',
            'term2.required' => 'Please select to term.',
            'bookuse2.required' => 'Please specify to book usage.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        if ($request->get('ref')) {
            $ref_code = $request->get('ref');
        } else {
            $ref_code = '';
        }
        $courses_pending_id = $request->get('courses_pending_id');
        $bookq = bookuse::find($request->get('bookuse'));
        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));

        if ($bookq->type == 1) {
            $mprice = $levelq->price;
            $mbprice = $levelq->book_price;
            $fprice = $levelq->price;
        } elseif ($bookq->type == 2) {
            $mprice = $levelq->half_price;
            $mbprice = $levelq->book_half_price;
            $fprice = $levelq->half_price;
        }

        if (!empty($request->get('level2'))) {

            $bookq2 = bookuse::find($request->get('bookuse2'));
            $levelq2 = Level::find($request->get('level2'));
            $termq2 = Term::find($request->get('term2'));

            $mprice = 0;
            $mbprice = 0;
            $coin = 0;
            for ($i = $levelq->id; $i <= $levelq2->id; $i++) {
                $sbook = 1;

                if ($i == $levelq->id) {
                    $sterm = $termq->name;
                } else {
                    $sterm = 1;
                }

                if ($i == $levelq2->id) {
                    $eterm = $termq2->name;
                } else {
                    $eterm = 4;
                }

                for ($j = $sterm; $j <= $eterm; $j++) {
                    if ($i == $levelq->id && $j == $sterm) {
                        $sbook = $bookq->type;
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $sbook = $bookq->type;
                    } else {
                        $sbook = 1;
                    }
                    //echo $j . "<br>";
                    /*  $books = bookuse::where('type', '=', $sbook)
                        ->where('level_id', '=', $i)
                        ->where('term_id', '=', $j)
                        ->get(); */
                    $books = bookuse::select(
                        "bookuses.*",
                        "bookuses.type as level_type",
                        "levels.name as level_name",
                        "levels.price as price",
                        "levels.half_price as half_price",
                        "levels.book_price as book_price",
                        "levels.book_half_price as book_half_price",
                        "terms.name as term_name",
                    )
                        ->join("terms", "terms.id", "=", "bookuses.term_id")
                        ->join("levels", "levels.id", "=", "bookuses.level_id");

                    if ($i == $levelq->id && $j == $sterm) {
                        $books->where('bookuses.id', '=', $bookq->id);
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $books->where('bookuses.id', '=', $bookq2->id);
                    } else {
                        $books->where('type', '=', $sbook)->where('bookuses.level_id', '=', $i)
                            ->where('bookuses.term_id', '=', $j);
                    }
                    $books = $books->get();
                    $bookss[] = $books;

                    if ($books[0]->level_type == 1) {
                        $mprice = $mprice + $books[0]->price;
                        $mbprice = $mbprice + $books[0]->book_price;
                    } else {
                        $mprice = $mprice + $books[0]->half_price;
                        $mbprice = $mbprice + $books[0]->book_half_price;
                    }

                    $coin++;
                }
            }


            if ($bookq2->type == 1) {
                $fprice2 = $levelq2->price;
            } elseif ($bookq2->type == 2) {
                $fprice2 = $levelq2->half_price;
            }
        } else {
            $books = bookuse::select(
                "bookuses.*",
                "bookuses.type as level_type",
                "levels.name as level_name",
                "levels.price as price",
                "levels.half_price as half_price",
                "levels.book_price as book_price",
                "levels.book_half_price as book_half_price",
                "terms.name as term_name",
            )
                ->join("terms", "terms.id", "=", "bookuses.term_id")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('bookuses.level_id', '=', $request->get('level'))
                ->where('bookuses.term_id', '=', $request->get('term'))
                ->where('bookuses.id', '=', $request->get('bookuse'))
                ->get();
            $bookss[] = $books;
        }

        // dd($coin);

        $input = $request->all();
        $input['level_name'] = $levelq->name;
        $input['term_name'] = $termq->name;
        $input['bookuse_name'] = $bookq->name;

        if (!empty($request->get('level2'))) {
            $input['level2_name'] = $levelq2->name;
            $input['term2_name'] = $termq2->name;
            $input['bookuse2_name'] = $bookq2->name;
        }


        if ($courses_pending_id) {

            $courses_pending = CoursePending::find($courses_pending_id);

            if ($courses_pending->status == 0) {
                # code...

                $courses_pending_id = null;
            } else {
                $status = 'wait';
                $this->updateWaitingForPayment($status, $courses_pending_id);
            }
        }
        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $request->get('student');
        $input_order['order_number'] = OrderRunningNumber::generate($request->get('centre'));
        $input_order['total_price'] = $mprice + $mbprice;
        $input_order['ref'] = $ref_code;
        $input_order['courses_pending_id'] = $courses_pending_id;
        $input_order['free_course'] = $request->get('free_course', 0);
        $input_order['free_course_reason'] = $request->get('free_course_reason');
        $order = Order::create($input_order);

        foreach ($bookss as $dresult) {
            foreach ($dresult as $book) {
                if ($book->level_type == 1) {
                    $tprice = $book->price;
                    $bprice = $book->book_price;
                } elseif ($book->level_type == 2) {
                    $tprice = $book->half_price;
                    $bprice = $book->book_half_price;
                }
                OrderDetailList::create([
                    'order_id' => $order->id,
                    'level' => $book->level_name,
                    'term' => $book->term_name,
                    'book' => $book->name,
                    'price' => $tprice,
                    'bprice' => $bprice,
                ]);
            }
        }

        $contact = Contact::where('id', $request->get('student'))->first();
        $contact->ref = $ref_code;
        $contact->free_course = $request->get('free_course');
        $contact->order = $order->id;
        $contact->level = $request->get('level');
        $contact->term = $request->get('term');
        $contact->bookuse = $request->get('bookuse');
        $contact->level_name = $levelq->name;
        $contact->term_name = $termq->name;
        $contact->bookuse_name = $bookq->name;
        $contact->level2 = $request->get('level2');
        $contact->term2 = $request->get('term2');
        $contact->bookuse2 = $request->get('bookuse2');
        $contact->level2_name = $levelq2->name;
        $contact->term2_name = $termq2->name;
        $contact->bookuse2_name = $bookq2->name;
        $contact->discontinued = 0;
        $contact->discontinued_date = null;
        $contact->discontinued_reason = null;
        $contact->save();

        $input_order_detail['order_id'] = $order->id;
        $input_order_detail['product_id'] = $bookq->id;
        $input_order_detail['pname'] = $bookq->name;
        $input_order_detail['punit'] = $bookq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $fprice;
        $order_detail = OrderDetail::create($input_order_detail);

        if (!empty($request->get('level2'))) {
            $input_order_detail2['order_id'] = $order->id;
            $input_order_detail2['product_id'] = $bookq2->id;
            $input_order_detail2['pname'] = $bookq2->name;
            $input_order_detail2['punit'] = $bookq2->unit;
            $input_order_detail2['quantity'] = 1;
            $input_order_detail2['price'] = $fprice2;
            $order_detail2 = OrderDetail::create($input_order_detail2);
        }

        // dd($request->all());
        if ($request->get('ref')) {
            $commmissionList = create_commmission_list($order->id, $courses_pending_id, $request->get('ref')); // on helper
        }

        $select_list_contact = '<option value="' . $contact->id . '" > ' . $contact->name . '</option>';
        return response()->json([
            'success' => 'Added Receipt successfully.',
            'contact' => $select_list_contact,
            'cid' => $contact->id,
            'oid' => $order->id,
            'coin' => $coin,
        ]);
    }

    public function gennerateStudent()
    {
        $students = Contact::orderBy('id')->get();

        return view('contacts.gennerate_student', ['students' => $students]);
        // dd($students);
    }
    public function gennerateStudentSave(Request $request)
    {

        // dd($request->all());
        $student_id = $request->student_id;
        if (!$student_id) {
            return response()->json(['errors' => ['Pls select student']]);
        }
        $pass = $request->password;
        $student = Contact::find($student_id);
        $action = $request->action;
        $text = 'eiMaths_';

        if ($action === 'generate') {
            $password = $request->password;
        } elseif ($action === 'random') {
            # code...
            $ranPass = Str::random(4);
            $password = $student->code . '_' . $ranPass;
            return response()->json(['success' => 'Random generate student password success !!', 'password' => $password]);
        } elseif ($action === 'update') {
            # code...
        } else {
            return response()->json(['error' => ['There is no action.']]);
        }

        $passwordHash = Hash::make($password);
        $student->update(['password' => $passwordHash]);

        return response()->json(['success' => 'Generate student password success !!', 'password' => $password]);
    }

    public function studentsLogin()
    {

        return view('contacts.login');
    }
    public function studentsCheckLogin(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'password' => 'required',
        ]);

        $student = Contact::where('code', $request->code)->first();

        if ($student && Hash::check($request->password, $student->password)) {
            Auth::guard('student')->login($student);
            $student = Auth::guard('student')->user();
            if ($student->type === 'demo') {
                # code...
                // return redirect()->route('home.section', ['P6', '4', '20']);
                return redirect()->route('home3');
            } else {
                # code...
                return redirect()->route('home3');
            }
        } else {
            return back()->withErrors(['error' => 'Invalid student code or password.']);
        }
    }
    public function studentsLogout()
    {

        Auth::guard('student')->logout();
        Session::flush();
        return redirect()->route('student.login');
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
        /*  $data = Order::select(
            "contacts.*",
            "orders.status as order_status",
            "departments.name as centre_name",
        )
            ->join("contacts", "contacts.id", "=", "orders.cid")
            ->join("departments", "contacts.centre", "=", "departments.id")
            ->where('orders.cid', $datac->id)
            ->orderBy("orders.id", "desc")->first(); */
        // dd($data);
        $datas = OrderDetail::select(
            "order_details.*",
            "levels.id as level_id",
            "terms.id as term_id",
        )
            ->join("bookuses", "bookuses.id", "=", "order_details.product_id")
            ->join("terms", "terms.id", "=", "bookuses.term_id")
            ->join("levels", "levels.id", "=", "bookuses.level_id")
            ->where('order_id', $data->order)->orderBy("id", "asc")->get();
        return response()->json(['data' => $data, 'datas' => $datas]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        // dd($request->all());
        $rules = [
            'name' => 'required|string|max:255|unique:contacts,name,' . $id,
            'start_date' => 'required|string|max:10',
            //'start_term' => 'required',
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

        $bookq = bookuse::find($request->get('bookuse'));
        $levelq = Level::find($request->get('level'));
        $termq = Term::find($request->get('term'));

        if ($bookq->type == 1) {
            $mprice = $levelq->price;
            $mbprice = $levelq->book_price;
            $fprice = $levelq->price;
        } elseif ($bookq->type == 2) {
            $mprice = $levelq->half_price;
            $mbprice = $levelq->book_half_price;
            $fprice = $levelq->half_price;
        }

        if (!empty($request->get('level2'))) {

            $bookq2 = bookuse::find($request->get('bookuse2'));
            $levelq2 = Level::find($request->get('level2'));
            $termq2 = Term::find($request->get('term2'));

            $mprice = 0;
            $mbprice = 0;
            for ($i = $levelq->id; $i <= $levelq2->id; $i++) {
                $sbook = 1;

                if ($i == $levelq->id) {
                    $sterm = $termq->name;
                } else {
                    $sterm = 1;
                }

                if ($i == $levelq2->id) {
                    $eterm = $termq2->name;
                } else {
                    $eterm = 4;
                }

                for ($j = $sterm; $j <= $eterm; $j++) {
                    if ($i == $levelq->id && $j == $sterm) {
                        $sbook = $bookq->type;
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $sbook = $bookq->type;
                    } else {
                        $sbook = 1;
                    }
                    //echo $j . "<br>";
                    /*  $books = bookuse::where('type', '=', $sbook)
                        ->where('level_id', '=', $i)
                        ->where('term_id', '=', $j)
                        ->get(); */
                    $books = bookuse::select(
                        "bookuses.*",
                        "bookuses.type as level_type",
                        "levels.name as level_name",
                        "levels.price as price",
                        "levels.half_price as half_price",
                        "levels.book_price as book_price",
                        "levels.book_half_price as book_half_price",
                        "terms.name as term_name",
                    )
                        ->join("terms", "terms.id", "=", "bookuses.term_id")
                        ->join("levels", "levels.id", "=", "bookuses.level_id");

                    if ($i == $levelq->id && $j == $sterm) {
                        $books->where('bookuses.id', '=', $bookq->id);
                    } else if ($i == $levelq2->id && $j == $eterm) {
                        $books->where('bookuses.id', '=', $bookq2->id);
                    } else {
                        $books->where('type', '=', $sbook)->where('bookuses.level_id', '=', $i)
                            ->where('bookuses.term_id', '=', $j);
                    }
                    $books = $books->get();

                    $bookss[] = $books;

                    if ($books[0]->level_type == 1) {
                        $mprice = $mprice + $books[0]->price;
                        $mbprice = $mbprice + $books[0]->book_price;
                    } else {
                        $mprice = $mprice + $books[0]->half_price;
                        $mbprice = $mbprice + $books[0]->book_half_price;
                    }
                }
            }


            if ($bookq2->type == 1) {
                $fprice2 = $levelq2->price;
            } elseif ($bookq2->type == 2) {
                $fprice2 = $levelq2->half_price;
            }
        } else {
            $request->merge(['level2' => 0]);
            $request->merge(['term2' => 0]);
            $request->merge(['bookuse2' => 0]);

            $books = bookuse::select(
                "bookuses.*",
                "bookuses.type as level_type",
                "levels.name as level_name",
                "levels.price as price",
                "levels.half_price as half_price",
                "levels.book_price as book_price",
                "levels.book_half_price as book_half_price",
                "terms.name as term_name",
            )
                ->join("terms", "terms.id", "=", "bookuses.term_id")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('bookuses.level_id', '=', $request->get('level'))
                ->where('bookuses.term_id', '=', $request->get('term'))
                ->where('bookuses.id', '=', $request->get('bookuse'))
                ->get();
            $bookss[] = $books;
        }

        $contactd = [
            'name' => $request->get('name'),
            'free_course' => $request->get('free_course'),
            'nickname' => $request->get('nickname'),
            'address' => $request->get('address'),
            'postcode' => $request->get('postcode'),
            'telephone' => $request->get('telephone'),
            'centre' => $request->get('centre'),
            'code' => $request->get('code'),
            'start_date' => $request->get('start_date'),
            'start_term' => $request->get('start_term'),
            'school' => $request->get('school'),
            'birth_date' => $request->get('birth_date'),
            'gender' => $request->get('gender'),
            'father_name' => $request->get('father_name'),
            'father_email' => $request->get('father_email'),
            'father_mobile' => $request->get('father_mobile'),
            'mother_name' => $request->get('mother_name'),
            'mother_email' => $request->get('mother_email'),
            'mother_mobile' => $request->get('mother_mobile'),
            'level' => $request->get('level'),
            'term' => $request->get('term'),
            'bookuse' => $request->get('bookuse'),
            'level_name' => $levelq->name,
            'term_name' => $termq->name,
            'bookuse_name' => $bookq->name,
            'level2' => $request->get('level2'),
            'term2' => $request->get('term2'),
            'bookuse2' => $request->get('bookuse2'),
            'level2_name' => !empty($levelq2->name) ? $levelq2->name : null,
            'term2_name' => !empty($termq2->name) ? $termq2->name : null,
            'bookuse2_name' => !empty($bookq2->name) ? $bookq2->name : null,
            'discontinued' => $request->get('discontinued'),
            'discontinued_date' => $request->get('discontinued_date'),
            'discontinued_reason' => $request->get('discontinued_reason'),
        ];

        $contact = Contact::find($id);
        $contact->update($contactd);

        $order = Order::find($contact->order);
        $input_order['centre'] = $request->get('centre');
        $input_order['cid'] = $contact->id;
        $total_price = (($mprice + $mbprice + $order->refund + $order->register_fee + $order->access_fee)) - ($order->discount + $order->discount_book);
        $input_order['total_price'] = $total_price;

        $order = Order::find($contact->order);

        if ($order) {
            $order->update($input_order);
        }

        $orderDetailsLToDelete = OrderDetailList::where('order_id', $order->id)->get();
        $orderDetailsLToDelete->each->delete();


        foreach ($bookss as $dresult) {
            foreach ($dresult as $book) {
                if ($book->level_type == 1) {
                    $tprice = $book->price;
                    $bprice = $book->book_price;
                } elseif ($book->level_type == 2) {
                    $tprice = $book->half_price;
                    $bprice = $book->book_half_price;
                }

                OrderDetailList::create([
                    'order_id' => $contact->order,
                    'level' => $book->level_name,
                    'term' => $book->term_name,
                    'book' => $book->name,
                    'price' => $tprice,
                    'bprice' => $bprice,
                ]);
            }
        }

        $orderDetailsToDelete = OrderDetail::where('order_id', $order->id)->get();
        $orderDetailsToDelete->each->delete();

        $input_order_detail['order_id'] = $order->id ?? null; // Set to null if $order is not found
        $input_order_detail['product_id'] = $bookq->id;
        $input_order_detail['pname'] = $bookq->name;
        $input_order_detail['punit'] = $bookq->unit;
        $input_order_detail['quantity'] = 1;
        $input_order_detail['price'] = $fprice;
        /*  $order_detail = OrderDetail::where('order_id', $input_order_detail['order_id'])->first();

        if ($order_detail) {
            $order_detail->update($input_order_detail);
        } */
        $order_detail = OrderDetail::create($input_order_detail);

        if (!empty($request->get('level2'))) {
            $input_order_detail2['order_id'] = $order->id ?? null;
            $input_order_detail2['product_id'] = $bookq2->id;
            $input_order_detail2['pname'] = $bookq2->name;
            $input_order_detail2['punit'] = $bookq2->unit;
            $input_order_detail2['quantity'] = 1;
            $input_order_detail2['price'] = $fprice2;
            /* $order_detail2 = OrderDetail::where('order_id', $input_order_detail['order_id'])
                ->where('id', '!=', $order_detail->id)
                ->first();
            if ($order_detail2) {
                $order_detail2->update($input_order_detail2);
            } */
            $order_detail2 = OrderDetail::create($input_order_detail2);
        }

        Receipt::where('cid', $id)->update([
            'level' => $request->get('level'),
            'total_fee' => $total_price,
            // Add more columns as needed
        ]);


        return response()->json(['success' => 'Student information updated successfully', 'discontinued' => $contact->discontinued]);
    }


    public function fastHistory($id)
    {
        $student = Contact::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $maxHistory = $this->maxHistory($student->id);
        if ($maxHistory < 1) {
            return response()->json(['message' => 'นักเรียนคนนี้เข้าเรียนครบแล้ว'], 404);
        }

        $history = Histrories::where('student_id', $student->id)
            ->where('term', $student->term)
            ->where('level_id', $student->level)
            ->orderByDesc('date')
            ->first();

        $now = Carbon::now();
        $startDate = Carbon::parse($history->start_date ?? $now->format('Y-m-d'));
        $endDate = $startDate->copy()->addMonths(4);

        $payload = [
            'centre' => $history->centre ?? $student->centre,
            'teacher_id' => $history->teacher_id ?? null,
            'student_id' => $student->id,
            'level_id' => $history->level_id ?? $student->level,
            'level_name' => $history->level_name ?? $student->level_name,
            'term' => $history->term ?? $student->term,
            'bookuse' => $history->bookuse ?? $student->bookuse,
            'date' => $now->toDateString(),
            'stime' => $now->format('H:i:s'),
            'etime' => $now->addHours(2)->format('H:i:s'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'comment' => null,
            'signature' => null,
        ];

        if ($history) {
            $remaining = max(($history->course_remaining ?? 0) - 1, 0);
            $payload['course_remaining'] = $remaining;
        } else {
            $payload['course_remaining'] = 9;
        }

        $newHistory = Histrories::create($payload);

        return response()->json(['data' => $newHistory]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Contact::find($id)->delete();
        $this->order_clear($id);
        $this->receive_clear($id);

        return ['success' => true, 'message' => 'Deleted student successfully'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Contact::find($arr_del[$xx])->delete();
            $this->order_clear($arr_del[$xx]);
            $this->receive_clear($arr_del[$xx]);
        }

        return redirect('/contacts')->with('success', 'Deleted student successfully');
    }

    public function order_clear($id)
    {
        try {
            DB::beginTransaction();

            // Find and delete orders where 'status' is '1'
            $deletedOrderIds = Order::where('cid', $id)->pluck('id')->toArray();
            Order::where('cid', $id)->delete();

            // Delete related records from the 'order_detail' table
            if (!empty($deletedOrderIds)) {
                DB::table('order_details')->whereIn('order_id', $deletedOrderIds)->delete();
            }

            DB::commit();

            // Commit the transaction if everything was successful
        } catch (\Exception $e) {
            DB::rollBack();

            // Handle the exception or show an error message
            // For example: Log the error, return an error response, etc.
        }
    }

    public function receive_clear($id)
    {
        try {
            DB::beginTransaction();
            Receipt::where('cid', $id)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }


    public function lfind($type, $level)
    {
        //$select_list = "<option value=''>please select</option>";
        $select_list = "";
        // dd($level);
        if ($type == 'add') {
            $data = term::select(
                "terms.id as id",
                "terms.name as name",
            )

                ->where('level_id', $level)
                ->where('status', 1)
                ->orderBy("terms.name", "asc")
                ->get();

            foreach ($data as $key) {
                $select_list .= '<option value="' . $key->name . '" >' . $key->name . '</option>';
            }
            // dd($select_list);
            return response()->json([
                'html' =>  $select_list
            ]);
        }
    }


    public function tfind(Request $request, $type, $level, $term)
    {
        //$select_list = "<option value=''>please select</option>";
        // dd($request);
        $select_list = "";
        if ($type == 'add') {
            $data = bookuse::select(
                "bookuses.id as id",
                "bookuses.name as name",
            )

                ->where('level_id', $level)
                ->where('term_id', $term)
                //->where('type', 1)
                ->where('status', 1)
                ->orderBy("bookuses.name", "asc")
                ->get();

            foreach ($data as $key) {
                $select_list .= '<option value="' . $key->id . '" >' . $key->name . '</option>';
            }

            $levelq = Level::find($request->get('level'));
            $termq = Term::find($request->get('term'));
            $coin = 0;

            if (!empty($request->get('level2'))) {

                $levelq2 = Level::find($request->get('level2'));
                $termq2 = Term::find($request->get('term2'));

                for ($i = $levelq->id; $i <= $levelq2->id; $i++) {

                    if ($i == $levelq->id) {
                        $sterm = $termq->name;
                    } else {
                        $sterm = 1;
                    }

                    if ($i == $levelq2->id) {
                        $eterm = $termq2->name;
                    } else {
                        $eterm = 4;
                    }

                    for ($j = $sterm; $j <= $eterm; $j++) {

                        $coin++;
                    }
                }
            }

            $result_coin = $coin * 10;

            return response()->json([
                'html' =>  $select_list,
                'scoin' => $result_coin,
            ]);
        }
    }

    // public function sCoin(Request $request,)
    // {

    //     $book = $request->get('book');
    //     $level = $request->get('level');
    //     $term = $request->get('term');

    //     $book2 = $request->get('book2');
    //     $level2 = $request->get('level2');
    //     $term2 = $request->get('term2');

    //     $result_coin = 0;
    //     $check = false;
    //     $type_book1 = bookuse::find($book)->type;
    //     $type_book2 = bookuse::find($book2)->type;

    //     if ($type_book1 == 1 && $type_book2 == 1) {
    //         $full_books = bookuse::whereBetween('id', [$book, $book2])
    //             ->where('type', 1)
    //             ->get();
    //     } elseif ($type_book1 == 2 && $type_book2 == 2) {
    //         $full_books = bookuse::whereBetween('id', [$book, $book2])
    //             ->where('type', 2)
    //             ->get();
    //     } else {
    //         $full_books = bookuse::whereBetween('level_id', [$level, $level2])
    //             ->whereBetween('term_id', [$term, $term2])
    //             ->where('type', 1)

    //             ->get();
    //         $check = true;
    //     }

    //     foreach ($full_books as $full_book) {

    //         $result_coin += $full_book->qty;
    //     }

    //     if ($check) {
    //         $result_coin = $result_coin - 5;
    //     }

    //     return response()->json([
    //         'scoin' => $result_coin,
    //     ]);
    // }

    public function sCoin(Request $request)
    {
        $book     = $request->get('book');
        $book2    = $request->get('book2');
        $level    = $request->get('level');
        $level2   = $request->get('level2');
        $term     = $request->get('term');
        $term2    = $request->get('term2');

        $result_coin = 0;
        $check = false;

        $book1_model = bookuse::find($book);
        $book2_model = bookuse::find($book2);

        // Safety check if books exist
        if (!$book1_model || !$book2_model) {
            return response()->json([
                'error' => 'One or both books not found.'
            ], 404);
        }

        $type1 = $book1_model->type;
        $type2 = $book2_model->type;

        if ($type1 === $type2) {
            // Same type, filter by book ID range
            $full_books = bookuse::whereBetween('id', [$book, $book2])
                ->where('type', $type1)
                ->get();
        } else {
            // Different types, fallback to level/term-based filtering
            $full_books = bookuse::whereBetween('level_id', [$level, $level2])
                ->whereBetween('term_id', [$term, $term2])
                ->where('type', 1)
                ->get();
            $check = true;
        }

        $result_coin = $full_books->sum('qty');

        if ($check) {
            $result_coin = max(0, $result_coin - 5); // prevent negative coin
        }

        return response()->json([
            'scoin' => $result_coin,
        ]);
    }


    public function exportContact(Request $request)
    {

        if ($request->excel === "1") {
            # code...
            return Excel::download(new ExportExcel($request->get('table_records')), 'ReceiptForm.xlsx');
        } else {

            # code...
            return Excel::download(new ExportForm($request->get('table_records')), 'ExportContact.xlsx');
        }
    }

    /**
     * Export contacts from centre 2 and 3, grouped by centre, ordered by id desc
     */
    public function exportContactsByCentre()
    {
        return Contact::exportToExcel('contacts_centre_2_3_export.xlsx');
    }

    /**
     * Get contacts data for centres 2 and 3 (for preview)
     */
    public function getContactsByCentre()
    {
        $contacts = Contact::getContactsByCentre();

        return response()->json([
            'success' => true,
            'data' => $contacts,
            'total_centre_2' => $contacts->has(2) ? $contacts[2]->count() : 0,
            'total_centre_3' => $contacts->has(3) ? $contacts[3]->count() : 0,
            'total' => $contacts->flatten()->count()
        ]);
    }
}
