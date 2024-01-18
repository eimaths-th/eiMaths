<?php

namespace App\Http\Controllers;

use App\Exports\ExportReciept;
use Illuminate\Http\Request;
use App\Models\Receipt;
use App\Models\ReceiptRunningNumber;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\term;
use App\Models\Sterm;
use App\Models\FileStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Interfaces\MoneyConvertServiceInterface;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReceiptController extends Controller
{

    protected $MoneyConvertService;
    //payment
    const datap = array(array("id" => "1", "name" => "เงินสด/Cash"), array("id" => "2", "name" => "โอนเงิน/Transfer"), array("id" => "3", "name" => "บัตรเครดิต/Credit Card"));

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(MoneyConvertServiceInterface $MoneyConvertService)
    {
        $this->middleware('permission:receipt-list|receipt-show|receipt-create|receipt-edit|receipt-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:receipt-show', ['only' => ['show']]);
        $this->middleware('permission:receipt-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:receipt-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:receipt-delete', ['only' => ['destroy']]);

        $this->MoneyConvertService = $MoneyConvertService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $centre = Auth::user()->department->id;
        // $type = strtoupper(Department::getDepartmentCodeById($centre));
        // dd($type);
        if ($request->ajax()) {
            //sleep(2);
            if ($request->get('type')) {
                $type = $request->get('type');
            } else {
                $type = 0;
            }

            $query = Receipt::select(
                "receipts.id as id",
                "receipts.receipt_number as receipt_number",
                "receipts.total_fee as total_fee",
                "receipts.payment_date as payment_date",
                //"receipts.level as level",
                //"receipts.status as order_status",
                "contacts.name as contact_name",
                "contacts.nickname as contact_nickname",
                "contacts.code as contact_code",
                //"contacts.start_term as start_term",
                "departments.name as centre",
                DB::raw("GROUP_CONCAT(order_detail.pname SEPARATOR ' - ') as book_use"),
                DB::raw("GROUP_CONCAT(levels.name SEPARATOR ' - ') as product_level"),
                DB::raw("SUM(order_detail.quantity) as total_amount")
            )
                ->join("contacts", "contacts.id", "=", "receipts.cid")
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->leftJoin("order_details as order_detail", "order_detail.order_id", "=", "receipts.oid")
                ->join("bookuses", "bookuses.id", "=", "order_detail.product_id")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('receipts.type', $type);

            if (!Gate::allows('all-centre')) {
                $query->where('receipts.centre', Auth::user()->department->id);
            }

            if (!empty($request->get('sdate'))) {
                //dd($request->get('sdate'));
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('receipts.payment_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    }
                }
            }
            $datas = $query->groupBy(
                'receipts.id',
                'receipts.receipt_number',
                'receipts.total_fee',
                'receipts.payment_date',
                'contacts.name',
                'contacts.nickname',
                'contacts.code',
                'departments.name'
            )
                ->orderBy("receipts.id", "desc")
                ->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->editColumn('contact_name', function ($row) {
                    $nic = $row->contact_nickname !== null && $row->contact_nickname !== '' ? '(' . $row->contact_nickname . ')' : '';
                    return $row->contact_name . ' ' . $nic;
                })
                ->editColumn('book_use', function (Receipt $datas) {

                    if (strpos($datas->book_use, " - ") !== false) {
                        $expn = explode(" - ", $datas->book_use);
                        return "(" . $expn[0] . ") - (" . $expn[1] . ")";
                    } else {
                        return $datas->book_use;
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::allows('order-show')) {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print" id="getPrintData" data-id="' . $row->id . '"><i class="fa fa-print"></i> Print</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-print"></i> Print</button> ';
                    }
                    if (Gate::allows('order-show')) {
                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-share" id="getShareData" data-id="' . $row->id . '"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-share disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                    }
                    return "<div class='col-sm-2 col-md-2 col-xl-2'>" . $html . "</div>";
                })->rawColumns(['checkbox', 'action', 'total_fee'])->toJson();
        }

        return view('receipts.index');
    }


    public function pending_term(Request $request)
    {
        if ($request->ajax()) {
            $query = Receipt::select(
                "receipts.id as id",
                "receipts.created_at as created_at",
                "receipts.total_fee as total_fee",
                "receipts.payment_date as payment_date",
                "receipts.level as level",
                "receipts.status as receipts_status",
                "contacts.name as contact_name",
                "contacts.nickname as contact_nickname",
                "contacts.code as contact_code",
                "contacts.start_term as start_term",
                "departments.name as centre",
                "bookuses.name as book_name",
                "bookuses.level_id as book_level",
                "bookuses.term_id as book_term",
                DB::raw('(CASE WHEN bookuses.term_id = 4 THEN (
                    SELECT bookuses.name
                    FROM bookuses
                    INNER JOIN levels ON levels.id = bookuses.level_id
                    WHERE bookuses.level_id > book_level
                    ORDER BY bookuses.level_id
                    LIMIT 1
                ) ELSE (
                    SELECT bookuses.name
                    FROM bookuses
                    INNER JOIN levels ON levels.id = bookuses.level_id
                    WHERE bookuses.level_id = book_level
                    AND bookuses.term_id > book_term
                    ORDER BY bookuses.id
                    LIMIT 1
                ) END) AS next_book_name'),
                DB::raw('(CASE WHEN bookuses.term_id = 4 THEN (
                    SELECT levels.price
                    FROM bookuses
                    INNER JOIN levels ON levels.id = bookuses.level_id
                    WHERE bookuses.level_id > book_level
                    ORDER BY bookuses.level_id
                    LIMIT 1
                ) ELSE (
                    SELECT levels.price
                    FROM bookuses
                    INNER JOIN levels ON levels.id = bookuses.level_id
                    WHERE bookuses.level_id = book_level
                    AND bookuses.term_id > book_term
                    ORDER BY bookuses.id
                    LIMIT 1
                ) END) AS next_book_price')

            )

                ->join("contacts", "contacts.id", "=", "receipts.cid")
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("orders", "orders.id", "=", "receipts.oid")
                ->leftJoin("order_details as first_detail", function ($join) {
                    $join->on("first_detail.order_id", "=", "orders.id")
                        ->whereRaw("first_detail.id = (SELECT MIN(id) FROM order_details WHERE order_id = orders.id)");
                })
                ->join("bookuses", "bookuses.id", "=", "first_detail.product_id")
                ->where('receipts.type', 1)
                ->where('receipts.status', 0);
            if (!Gate::allows('all-centre')) {
                $query->where('receipts.centre', Auth::user()->department->id);
            }


            if (!empty($request->get('sdate'))) {
                //dd($request->get('sdate'));
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('receipts.created_at', [$startDate, $endDate]);
                    }
                }
            }
            $datas = $query->orderBy("receipts.id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('created_at', function (Receipt $datas) {
                    return date('Y-m-d', strtotime($datas->created_at));
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->editColumn('contact_name', function ($row) {
                    $nic = $row->contact_nickname !== null && $row->contact_nickname !== '' ? '(' . $row->contact_nickname . ')' : '';
                    return $row->contact_name . ' ' . $nic;
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::allows('order-edit')) {

                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-generate" data-book="' . $row->next_book_name . '" data-price="' . $row->next_book_price . '" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Generate Receipt</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Confirm
                        Order</button> ';
                    }
                    /* if (Gate::allows('order-show')) {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print" id="getPrintData" data-id="' . $row->id . '"><i class="fa fa-print"></i> Print</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-print"></i> Print</button> ';
                    }
                    if (Gate::allows('order-show')) {
                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-share" id="getShareData" data-id="' . $row->id . '"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-share disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                    } */
                    return "<div class='col-sm-2 col-md-2 col-xl-2'>" . $html . "</div>";
                })->rawColumns(['checkbox', 'action', 'next_price'])->toJson();
        }



        return view('receipts.pending_term');
    }

    /**
     * Display a listing of the resource.
     */
    public function pending(Request $request)
    {
        $new = $request->query('new');

        if ($request->ajax()) {
            //sleep(2);
            $query = Order::select(
                "orders.id as id",
                "orders.order_number as order_number",
                "orders.total_price as total_price",
                "orders.created_at as created_at",
                "orders.status as order_status",
                "contacts.name as contact_name",
                "contacts.nickname as contact_nickname",
                "contacts.code as contact_code",
                "contacts.start_term as start_term",
                "departments.name as centre"
            )
                ->addSelect(DB::raw("GROUP_CONCAT(order_detail.pname SEPARATOR ' - ') as product_name"))
                ->addSelect(DB::raw("GROUP_CONCAT(levels.name SEPARATOR ' - ') as product_level"))
                ->addSelect(DB::raw("SUM(order_detail.quantity) as total_amount"))
                ->join("contacts", "contacts.id", "=", "orders.cid")
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->leftJoin("order_details as order_detail", function ($join) {
                    $join->on("order_detail.order_id", "=", "orders.id");
                })
                ->join("bookuses", "bookuses.id", "=", "order_detail.product_id")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('orders.status', 0);

            if (!Gate::allows('all-centre')) {
                $query->where('orders.centre', Auth::user()->department->id);
            }

            if (!empty($request->get('sdate'))) {
                //dd($request->get('sdate'));
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    }
                }
            }
            $query->groupBy(
                "orders.id",
                "orders.order_number",
                "orders.total_price",
                "orders.created_at",
                "orders.status",
                "contacts.name",
                'contacts.nickname',
                "contacts.code",
                "contacts.start_term",
                "departments.name"
            );

            $datas = $query->orderBy("orders.id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('created_at', function (Order $datas) {
                    return date('Y-m-d H:i:s', strtotime($datas->created_at));
                })
                ->editColumn('contact_name', function ($row) {
                    $nic = $row->contact_nickname !== null && $row->contact_nickname !== '' ? '(' . $row->contact_nickname . ')' : '';
                    return $row->contact_name . ' ' . $nic;
                })
                ->editColumn('product_name', function (Order $datas) {

                    if (strpos($datas->product_name, " - ") !== false) {
                        $expn = explode(" - ", $datas->product_name);
                        return "(" . $expn[0] . ") - (" . $expn[1] . ")";
                    } else {
                        return $datas->product_name;
                    }
                })
                ->editColumn('total_cost', function (Order $datas) {
                    //$total_cost = $datas->amount * $datas->cost;
                    return '<font class="text-success">' . number_format($datas->total_cost, 2, '.', ',') . " บาท </font>";
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    if ($row->order_status == 1) {
                        if (Gate::allows('order-show')) {
                            $html = '<button type="button" class="btn btn-sm btn-success btn-print" id="getPrintData" data-id="' . $row->id . '"><i class="fa fa-print"></i> Print</button> ';
                        } else {
                            $html = '<button type="button" class="btn btn-sm btn-success btn-print disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-print"></i> Print</button> ';
                        }
                        if (Gate::allows('order-show')) {
                            $html .= '<button type="button" class="btn btn-sm btn-danger btn-share" id="getShareData" data-id="' . $row->id . '"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                        } else {
                            $html .= '<button type="button" class="btn btn-sm btn-danger btn-share disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                        }
                    }
                    if (Gate::allows('order-edit')) {

                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Create Receipt</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i>Create Receipt</button> ';
                    }
                    return "<div class='col-sm-2 col-md-2 col-xl-2'>" . $html . "</div>";
                })->rawColumns(['checkbox', 'action', 'total_cost'])->toJson();
        }


        $customData = [
            [
                'id' => 1,
                'name' => 'TermFee',
            ],
        ];

        $customCollection = collect($customData);
        $product = bookuse::orderBy("id", "asc")->get();
        /* where([['discontinued', '0']]) */
        $contact = Contact::/* where([['centre', Auth::user()->department->id]])
            -> */orderBy("id", "asc")->get();

        $centre = Department::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $term = term::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $sterm = Sterm::where([['status', '1']])
            ->orderBy("name", "asc")->get();
        $level = level::where([['status', '1']])
            ->orderBy("name", "asc")->get();

        return view('receipts.pending')->with(['product' => $product])
            ->with(['contact' => $contact])
            ->with(['order_type' => $customCollection])
            ->with(['centre' => $centre])
            ->with(['level' => $level])
            ->with(['term' => $term])
            ->with(['sterm' => $sterm])
            ->with(['new' => $new]);
    }


    /**
     * Display the specified resource.
     */
    public function show($id, $social = 0)
    {
        // Get the data from the database query
        $datas = Receipt::select(
            "receipts.*",
            "receipts.receipt_number as doc_number",
            "contacts.name as cname",
            "contacts.telephone as ctelephone",
            "contacts.code as ccode",
            "contacts.bookuse_name as book",
            "departments.name as centre_name",
            "orders.total_price as totalp",
            "orders.refund as refund",
            "orders.register_fee as register_fee",
            "orders.access_fee as access_fee",
            "orders.orther_fee as orther_fee",
            "orders.discount as discount",
            "orders.payment as payment",
            "orders.discount_book as discount_book",
            //"first_detail.price as price"
            DB::raw("CASE
        WHEN contacts.father_name IS NOT NULL AND contacts.father_name != '' THEN contacts.father_name
        WHEN contacts.mother_name IS NOT NULL AND contacts.mother_name != '' THEN contacts.mother_name
        ELSE NULL
    END AS parent_name"),
            DB::raw("CASE
        WHEN contacts.father_name IS NOT NULL AND contacts.father_name != '' THEN contacts.father_mobile
        WHEN contacts.mother_name IS NOT NULL AND contacts.mother_name != '' THEN contacts.mother_mobile
        ELSE NULL
    END AS parent_mobile")

        )
            ->join("contacts", "contacts.id", "=", "receipts.cid")
            ->join("orders", "orders.id", "=", "receipts.oid")
            /* ->leftJoin("order_details as first_detail", function ($join) {
                $join->on("first_detail.order_id", "=", "orders.id")
                    ->whereRaw("first_detail.id = (SELECT MIN(id) FROM order_details WHERE order_id = orders.id)");
            }) */
            ->join("departments", "contacts.centre", "=", "departments.id")
            ->where("receipts.id", $id)
            ->get();

        //dd($datas);

        $datas_list = OrderDetailList::select(
            //"order_detail_lists.*",
            "order_detail_lists.id as id",
            "order_detail_lists.price as oprice",
            "order_detail_lists.bprice as obprice",
            DB::raw("SUBSTRING(order_detail_lists.level, 1, 1) as first_char"),
            DB::raw("COUNT(*) as row_count"),
            DB::raw("SUM(order_detail_lists.price) as total_price"),
            DB::raw("SUM(eimatch.order_detail_lists.bprice) as total_bprice")
        )->where('order_id', $datas[0]->oid)
            ->groupBy(
                // 'order_detail_lists.level',
                'first_char',
                'order_detail_lists.price',
                'order_detail_lists.bprice',
                'order_detail_lists.id',
                // 'order_detail_lists.order_id',
                // 'eimatch.order_detail_lists.term',
                // 'order_detail_lists.book',
                // 'order_detail_lists.price',
                // 'eimatch.order_detail_lists.bprice',
                // 'eimatch.order_detail_lists.created_at',
                // 'eimatch.order_detail_lists.updated_at'
            )
            //->orderBy("id", "asc")
            ->get();

        //dd($datas_list);
        // dd(
        //     $id,
        //     $datas,
        //     $datas_list,

        // );

        $datasf = $this->getfileattprint($datas[0]->oid);

        // Check if the data exists before proceeding
        if ($datas->isEmpty()) {
            return redirect()->back()->with('error', 'Receipt data not found.');
        }

        // Load the template file from the 'templates' subdirectory
        $template = 'templates.print_receipt';
        $templates = 'templates.print_social';

        $ctype = "";
        $price_text = $this->MoneyConvertService->baht_text($datas[0]->total_fee);
        $price_texten = $this->MoneyConvertService->convertNumber($datas[0]->total_fee);

        $formattedDate = $datas[0]->created_at->format('Y-m-d');

        $qty = 0;
        $lprice = 0;
        $lbprice = 0;
        foreach ($datas_list as $price_list) {
            $ccname = $price_list->first_char;
            $qty = $qty + $price_list->row_count;
            $oprice = $price_list->oprice;
            $obprice = $price_list->obprice;
            $lprice = $lprice + $price_list->total_price;
            $lbprice = $lbprice + $price_list->total_bprice;
        }

        //dd($lbprice);

        // Data to pass to the view
        if ($datas_list[0]->first_char == "K") {
            $ccname = "Kindergarten";
        } else if ($datas_list[0]->first_char == "P") {
            $ccname = "Primary school";
        }

        $maxPrice = $datas_list->max('oprice');
        $maxBPrice = $datas_list->max('obprice');
        $book_price = $lbprice - $datas[0]->discount_book;
        $vat_price = ($lprice + $datas[0]->register_fee + $datas[0]->access_fee + $datas[0]->orther_fee + $datas[0]->refund) - $datas[0]->discount;
        $vat_val = $vat_price * 0.07;
        $net_price = $vat_price + $book_price;

        $customItem = [[
            'pname' => 'ค่าคอร์สเรียน ' . $ccname,
            'quantity' => $qty,
            'uprice' => $maxPrice,
            'price' => $lprice - $datas[0]->discount,
            'discount' => $datas[0]->discount,
            // Add other properties as needed
        ], [
            'pname' => 'ค่าหนังสือ',
            'quantity' => $qty,
            'uprice' => $maxBPrice,
            'price' => $lbprice - $datas[0]->discount_book,
            'discount' => $datas[0]->discount_book,
            // Add other properties as needed
        ]];

        if ($datas[0]->refund !== "" and $datas[0]->refund !== "0.00") {
            $refundItem = [
                [
                    'pname' => 'ค่ามัดจำ',
                    'quantity' => 1,
                    'uprice' => $datas[0]->refund,
                    'price' => $datas[0]->refund * 1,
                    'discount' => '0.00',

                ]
            ];
            $customItem = array_merge($customItem, $refundItem);
        };

        if ($datas[0]->register_fee !== "" and $datas[0]->register_fee !== "0.00") {
            $regisItem = [
                [
                    'pname' => 'ค่าลงทะเบียนแรกเข้า',
                    'quantity' => 1,
                    'uprice' => $datas[0]->register_fee * 1,
                    'price' => $datas[0]->register_fee,
                    'discount' => '0.00',

                ]
            ];
            $customItem = array_merge($customItem, $regisItem);
        };

        if ($datas[0]->access_fee !== "" and $datas[0]->access_fee !== "0.00") {
            $accessItem = [
                [
                    'pname' => 'ค่าอุปกรณ์การเรียน',
                    'quantity' => 1,
                    'uprice' => $datas[0]->access_fee * 1,
                    'price' => $datas[0]->access_fee,
                    'discount' => '0.00',

                ]
            ];
            $customItem = array_merge($customItem, $accessItem);
        };


        if ($datas[0]->orther_fee !== "" and $datas[0]->orther_fee !== "0.00") {
            $accessItem = [
                [
                    'pname' => 'รายได้อื่นๆ',
                    'quantity' => 1,
                    'uprice' => $datas[0]->orther_fee * 1,
                    'price' => $datas[0]->orther_fee,
                    'discount' => '0.00',

                ]
            ];
            $customItem = array_merge($customItem, $accessItem);
        };



        $data = [
            'ctype' => $ctype,
            'book_price' => $book_price,
            'vat_price' => $vat_price,
            'net_price' => $net_price,
            'price_text' => $price_text,
            'price_texten' => $price_texten,
            //'etype' => "ค่าสมัครเรียน / Application Fee",
            'date' => $formattedDate,
            'data' => $datas->first(),
            'items' => compact('customItem'),
            'file_att' => $datasf,
        ];

        //dd($data);


        if ($social == 1) {
            // Render the view with the data and get the HTML content
            $htmlContents = View::make($templates, $data)->render();

            // Generate the PDF
            $pdf = PDF::loadHTML($htmlContents);
            //$pdf->getDomPDF()->getFontMetrics()->setFont('YourThaiFont.ttf', 'YourThaiFont');

            // Save the PDF to a temporary file path
            $pdfPath = public_path('file_store/' . $datas[0]->doc_number . '.pdf');
            //dd($pdfPath);
            $pdf->save($pdfPath);

            // Define the file name for the downloaded PDF
            $pdfFileName = $datas[0]->doc_number . '.pdf';

            // Download the PDF file
            //return response()->download($pdfPath, $pdfFileName, ['Content-Type' => 'application/pdf']);
            return response(file_get_contents($pdfPath), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $pdfFileName . '"',
            ]);
        } else {
            // Render the view with the data and get the HTML content
            $htmlContent = View::make($template, $data)->render();
            //return new Response($htmlContent, 200, ['Content-Type' => 'text/html']);
            return response()->json([
                'html' =>  $htmlContent
            ]);
        }
    }

    public function getfileattprint($id)
    {
        $filestore = FileStore::where([['module', 'orders']])
            ->where([['module_id', $id]])
            ->orderBy("id", "asc")->get();

        $img = "";
        if (!$filestore->isEmpty()) {
            foreach ($filestore as $pics) {
                $imgf = url('/') . '/file_store/' . $pics->filename;
                $img .= "<img src=\"" . $imgf . "\" height=\"150\">&nbsp;&nbsp;&nbsp;";
            }
        } else {
            $img = "";
        }

        return $img;
    }

    /**
     * Update the specified resource in storage.
     */
    public function generate(Request $request)
    {
        $id = $request->get('id');
        $receipt = Receipt::find($id);

        /* if ($row->book_term == 4) {
            $nextbook = bookuse::select("bookuses.*", "levels.price as nprice")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('level_id', '>', $row->book_level)->orderBy('level_id')->first();
        } else {
            $nextbook = bookuse::select("bookuses.*", "levels.price as nprice")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('level_id', '=', $row->book_level)
                ->where('term_id', '>', $row->book_term)->orderBy('id')->first();
        }
       */

        //invoice
        $receipt->receipt_number = ReceiptRunningNumber::generate($receipt->centre);
        $receipt->level = $request->get('book');
        $receipt->total_fee = $request->get('price');
        $receipt->payment_date = date("Y-m-d");
        $receipt->status = 1;
        $receipt->save();


        return response()->json(['success' => true, 'message' => 'Generate Receipt successfully']);
    }

    public function exportReciept(Request $request)
    {

        return Excel::download(new ExportReciept($request->get('table_records')), 'ExportReciept.xlsx');
    }
}
