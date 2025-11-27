<?php

namespace App\Http\Controllers;

use App\Exports\ExportReciept;
use Illuminate\Http\Request;
use App\Models\Receipt;
use App\Models\receipt_details;
use App\Models\ReceiptRunningNumber;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\term;
use App\Models\Sterm;
use App\Models\FileUpload;
use App\Models\FileStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
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

    public function update_name($name, $id)
    {
        $receipt = Receipt::find($id);
        $receipt->update(['student_name' => $name]);
    }

    public function gen_receipt_data($id)
    {
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
            "receipts.payment as payment",
            "orders.discount_book as discount_book",
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
            ->join("departments", "contacts.centre", "=", "departments.id")
            ->where("receipts.id", $id)
            ->get();

        $datas_list = OrderDetailList::select(
            "order_detail_lists.id as id",
            "order_detail_lists.price as oprice",
            "order_detail_lists.bprice as obprice",
            DB::raw("SUBSTRING(order_detail_lists.level, 1, 1) as first_char"),
            DB::raw("COUNT(*) as row_count"),
            DB::raw("SUM(order_detail_lists.price) as total_price"),
            DB::raw("SUM(order_detail_lists.bprice) as total_bprice")
        )->where('order_id', $datas[0]->oid)
            ->groupBy(
                'first_char',
                'order_detail_lists.price',
                'order_detail_lists.bprice',
                'order_detail_lists.id',

            )->get();



        // Check if the data exists before proceeding
        if ($datas->isEmpty()) {
            return redirect()->back()->with('error', 'Receipt data not found.');
        }


        $qty = 0;
        foreach ($datas_list as $price_list) {
            $ccname = $price_list->first_char;
            $qty = $qty + $price_list->row_count;
        }


        // Data to pass to the view
        if ($datas_list[0]->first_char == "K") {
            $ccname = "Kindergarten";
        } else if ($datas_list[0]->first_char == "P") {
            $ccname = "Primary school";
        }

        $maxPrice = $datas_list->max('oprice');
        $maxBPrice = $datas_list->max('obprice');

        receipt_details::where('receipt_id', $id)->delete();

        $receipt_detail = new receipt_details();
        $receipt_detail->receipt_id = $id;
        $receipt_detail->des = 'ค่าคอร์สเรียน ' . $ccname;
        $receipt_detail->unit = '';
        $receipt_detail->quantity = $qty;
        $receipt_detail->price = $maxPrice;
        $receipt_detail->discount = $datas[0]->discount;
        $receipt_detail->tax = 1;
        $receipt_detail->save();

        $receipt_detail = new receipt_details();
        $receipt_detail->receipt_id = $id;
        $receipt_detail->des = 'ค่าหนังสือ';
        $receipt_detail->unit = '';
        $receipt_detail->quantity = $qty;
        $receipt_detail->price = $maxBPrice;
        $receipt_detail->discount = $datas[0]->discount_book;
        $receipt_detail->tax = 0;
        $receipt_detail->save();

        if ($datas[0]->refund !== "" and $datas[0]->refund !== "0.00") {
            $receipt_detail = new receipt_details();
            $receipt_detail->receipt_id = $id;
            $receipt_detail->des = 'ค่ามัดจำ';
            $receipt_detail->unit = '';
            $receipt_detail->quantity = 1;
            $receipt_detail->price = $datas[0]->refund;
            $receipt_detail->discount = '0.00';
            $receipt_detail->tax = 1;
            $receipt_detail->save();
        };

        if ($datas[0]->register_fee !== "" and $datas[0]->register_fee !== "0.00") {
            $receipt_detail = new receipt_details();
            $receipt_detail->receipt_id = $id;
            $receipt_detail->des = 'ค่าลงทะเบียนแรกเข้า';
            $receipt_detail->unit = '';
            $receipt_detail->quantity = 1;
            $receipt_detail->price = $datas[0]->register_fee;
            $receipt_detail->discount = '0.00';
            $receipt_detail->tax = 1;
            $receipt_detail->save();
        };

        if ($datas[0]->access_fee !== "" and $datas[0]->access_fee !== "0.00") {
            $receipt_detail = new receipt_details();
            $receipt_detail->receipt_id = $id;
            $receipt_detail->des = 'ค่าอุปกรณ์การเรียน';
            $receipt_detail->unit = '';
            $receipt_detail->quantity = 1;
            $receipt_detail->price = $datas[0]->access_fee;
            $receipt_detail->discount = '0.00';
            $receipt_detail->tax = 1;
            $receipt_detail->save();
        };
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

            $centre = $request->get('centre');

            // $query = Receipt::select(
            //     "receipts.id as id",
            //     "receipts.ref as ref",
            //     "receipts.receipt_number as receipt_number",
            //     "receipts.total_fee as total_fee",
            //     "receipts.payment_date as payment_date",
            //     //"receipts.level as level",
            //     //"receipts.status as order_status",
            //     //"contacts.name as contact_name",
            //     "receipts.student_name as contact_name",
            //     "contacts.nickname as contact_nickname",
            //     "contacts.code as contact_code",
            //     //"contacts.start_term as start_term",
            //     "departments.name as centre",
            //     DB::raw("GROUP_CONCAT(order_detail.pname SEPARATOR ' - ') as book_use"),
            //     DB::raw("GROUP_CONCAT(levels.name SEPARATOR ' - ') as product_level"),
            //     DB::raw("SUM(order_detail.quantity) as total_amount")
            // )
            //     ->join("contacts", "contacts.id", "=", "receipts.cid")
            //     ->join("departments", "contacts.centre", "=", "departments.id")
            //     ->leftJoin("order_details as order_detail", "order_detail.order_id", "=", "receipts.oid")
            //     ->join("bookuses", "bookuses.id", "=", "order_detail.product_id")
            //     ->join("levels", "levels.id", "=", "bookuses.level_id")
            //     ->where('receipts.type', $type);
            $query = Receipt::where('type', $type);
            // $test = $query->get();
            // dd($test);
            if ($centre && $centre !== '1') {
                $query->where('centre', $centre);
            }
            if ($request->date) { // e.g. "2025-10"
                [$year, $month] = explode('-', $request->date);
                $query->whereYear('payment_date', $year)
                      ->whereMonth('payment_date', $month);
            }
            // if ($reservationValue) {
            //     $query->where('receipts.payment_date', 'like', $reservationValue . '%');
            // }

            if (!Gate::allows('all-centre')) {
                $query->where('centre', Auth::user()->department->id);
            }
            // dd($request->all());
            if (!empty($request->get('sdate'))) {
                //dd($request->get('sdate'));
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('payment_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    }
                }
            }


            $datas = $query->orderBy("id", "desc")
                ->get();
            // dd($request->all(), $datas);
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    //$this->gen_receipt_data($row->id);
                    //$this->update_name($row->contact_name, $row->id);
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('centre', function ($row) {
                    return $row->department->name ?? 'bug';
                })
                ->editColumn('contact_code', function ($row) {
                    return $row->contact->code ?? 'bug';
                })
                ->editColumn('receipt_number', function ($row) {
                    return $row->receipt_number ?? 'bug';
                })
                ->editColumn('payment_date', function ($row) {
                    return $row->payment_date ?? 'bug';
                })
                ->editColumn('product_level', function ($row) {
                    return $row->levels->name ?? $row->level;
                })
                ->editColumn('book_use', function ($row) {
                    return '(' . $row->orderDetails->first()->pname . ') - (' . $row->orderDetails->last()->pname . ')';
                })
                ->editColumn('total_fee', function ($row) {
                    return $row->total_fee ?? 'bug';
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->editColumn('contact_name', function ($row) {
                    $nic = $row->contact->nickname ?? '';
                    //return $row->contact_name . ' ' . $nic;
                    $name = $row->contact->name ?? 'bug';
                    $text = $name . ' ( ' . $nic . ' )';
                    return $text;
                })
                ->editColumn('ref', function ($row) {
                    return $row->ref;
                })

                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::allows('receipt-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }
                    if (Gate::allows('order-show')) {
                        $html .= '<button type="button" class="btn btn-sm btn-success btn-print" id="getPrintData" data-id="' . $row->id . '"><i class="fa fa-print"></i> Print</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-success btn-print disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-print"></i> Print</button> ';
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

        $centre = $request->get('centre');
        $reservationValue = $request->get('date');

        if ($request->ajax()) {
            //sleep(2);
            $query = Order::select(
                "orders.id as id",
                "orders.ref as ref",
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

            if ($centre && $reservationValue) {
                $query->where('orders.centre', $centre);
                $query->where('orders.created_at', 'like', $reservationValue . '%');
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
                "orders.ref",
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
            // dd($datas);
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('created_at', function (Order $datas) {
                    return date('Y-m-d H:i:s', strtotime($datas->created_at));
                })
                ->editColumn('ref_code', function (Order $datas) {
                    return $datas->ref;
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
                    if (Gate::allows('order-delete')) {

                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '"><i class="fa-solid fa-ban"></i> Cancle</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa-solid fa-ban"></i> Cancle</button> ';
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

    public function price_text($price)
    {
        $price_text = $this->MoneyConvertService->baht_text($price);
        $price_texten = $this->MoneyConvertService->convertNumber($price);

        return response()->json([
            'th' => $price_text,
            'en' => $price_texten,
        ]);
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
            //"contacts.name as cname",
            "receipts.student_name as cname",
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
            "receipts.payment as payment",
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

        //  dd($datas->toArray());


        if ($datas->isEmpty()) {
            return redirect()->back()->with('error', 'Receipt data not found.');
        }

        $receipt_detail = receipt_details::where("receipt_id", $id)->orderby('id')->get();


        $template = 'templates.print_receipt';
        $templatee = 'templates.edit_receipt';
        $templates = 'templates.print_social';

        $total = 0;
        $discount_val = 0;
        $vat_price = 0;
        $book_price = 0;
        foreach ($receipt_detail as $price_list) {
            $total +=
                $price_list->price * $price_list->quantity - (int) $price_list->discount;
            $discount_val += (int) $price_list->discount;

            if ($price_list->tax == 0) {
                $book_price +=
                    $price_list->price * $price_list->quantity -
                    (int) $price_list->discount;
            }
        }
        $vat_price = $total - $book_price;
        $vatRate = 7;
        $vat_val = ($vat_price * $vatRate) / 107;
        $net_price = $vat_price + $book_price + $datas[0]->orther_fee;

        $ctype = "";
        //$formattedDate = $datas[0]->created_at->format('Y-m-d');
        $formattedDate = $datas[0]->payment_date;
        $price_text = $this->MoneyConvertService->baht_text($net_price);
        $price_texten = $this->MoneyConvertService->convertNumber($net_price);

        $data = [
            'ctype' => $ctype,
            'orther_fee' => $datas[0]->orther_fee,
            'price_text' => $price_text,
            'price_texten' => $price_texten,
            'vat_price' => $vat_price,
            'vat_val' => $vat_val,
            'book_price' => $book_price,
            'net_price' => $net_price,
            'discount_val' => $discount_val,
            'date' => $formattedDate,
            'data' => $datas->first(),
            'items' => $receipt_detail,
        ];

        // dd($data);


        if ($social == 1) {
            // Render the view with the data and get the HTML content
            $data['file_att'] =  $this->getfileattprint($datas[0]->oid);
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
        } else  if ($social == 2) {
            $data['file_att'] =  $this->getfileatt($datas[0]->oid);
            // Render the view with the data and get the HTML content
            $htmlContent = View::make($templatee, $data)->render();
            //return new Response($htmlContent, 200, ['Content-Type' => 'text/html']);
            return response()->json([
                'html' =>  $htmlContent
            ]);
        } else {
            // Render the view with the data and get the HTML content
            $data['file_att'] = $this->getfileattprint($datas[0]->oid);
            $htmlContent = View::make($template, $data)->render();
            // $htmlContent = "";
            //return new Response($htmlContent, 200, ['Content-Type' => 'text/html']);
            return response()->json([
                'html' =>  $htmlContent
            ]);
        }
    }

    public function getfileatt($id)
    {
        $filestore = FileStore::where([['module', 'orders']])
            ->where([['module_id', $id]])
            ->orderBy("id", "asc")->get();

        $img = "";
        if (!$filestore->isEmpty()) {
            foreach ($filestore as $pics) {
                $imgf = url('/') . '/file_store/' . $pics->filename;
                $img .= "<div id='img_" . $pics->id . "' class='col-md-4 text-center mb-3'><img src=\"" . $imgf . "\" height=\"150\"><br>
                <a class='btn btn-sm btn-info btn-view' href=\"" . urldecode($imgf) . "\" target=\"blank\"><i class='fa fa-search'></i></a>
                <a href='#' class='btn btn-sm btn-danger btn-edit' id='getDeleteData' data-id2='" . $pics->id . "' data-id='" . $id . "'><i class='fa fa-trash'></i></a>
                </div>
                <br><br>";
            }
        } else {
            $img = "";
        }

        return $img;
    }

    public function deleteImg($id, $id2)
    {
        $file = FileStore::find($id2);

        if ($file) {
            // Get the file path
            $filePath = public_path('file_store/' . $file->filename);
            if (file_exists($filePath)) {
                unlink($filePath);
                $file->delete();
            }
        }

        $html = $this->getfileatt($id);

        return response()->json(['imgs' => $html]);
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
    public function update(Request $request, $id)
    {

        $datao = [
            'orther_fee' => $request->get('charge'),
            'updated_by' => Auth::id()
        ];



        $data = [
            'receipt_number' => $request->get('receipt_number'),
            'payment_date' => $request->get('receipt_date'),
            'payment' => $request->get('payment'),
            'total_fee' => (float) $request->get('total'),
            'updated_by' => Auth::id()
        ];

        $receipt = Receipt::find($id);
        $receipt->update($data);

        Order::find($receipt->oid)->update($datao);
        receipt_details::where('receipt_id', $id)->delete();

        $amounts = $request->input('amount');
        $prices = $request->input('price');
        $des = $request->input('des');
        $unit = $request->input('unit');
        $discount = $request->input('discount');
        $tax = $request->input('tax');


        if (is_array($amounts) && is_array($prices) && count($amounts) === count($prices)) {
            foreach ($amounts as $index => $amount) {
                if (isset($amounts[$index])) { // Check if the index exists in $sids array
                    $rDetail = new receipt_details();
                    $rDetail->receipt_id = $id;
                    $rDetail->quantity = $amount;
                    $rDetail->price = $prices[$index];
                    $rDetail->des = $des[$index];
                    $rDetail->unit = $unit[$index];
                    $rDetail->discount = $discount[$index];
                    $rDetail->tax = $tax[$index];
                    $rDetail->save();
                }
            }
        }

        //file att

        $filesa = $request->get('img');

        if (!empty($filesa)) {
            foreach ($filesa as $filea) {
                //echo $filea;
                $oldfile = FileUpload::where('oldname', $filea)->get();
                $filestore = new FileStore();
                $filestore->module = 'orders';
                $filestore->module_id = $receipt->oid;
                $filestore->filename = $oldfile[0]->filename;
                $filestore->save();

                File::move(public_path() . '/file_upload/' . $oldfile[0]->filename, public_path() . '/file_store/' . $oldfile[0]->filename);
                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
        }

        return response()->json(['success' => 'แก้ไข ใบเสร็จรับเงิน เรียบร้อยแล้ว']);
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

    /**
     * API สรุปรายได้รายวัน (วันปัจจุบัน) แยกตามสาขา
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetRevenueSummaryByBranch(Request $request)
    {
        try {
            $current_date = date('Y-m-d');

            // Query รายได้แยกตามสาขา เฉพาะวันนี้
            $branch_revenue = Receipt::select(
                'receipts.centre',
                'departments.name as branch_name',
                'departments.code as branch_code',
                DB::raw('COUNT(receipts.id) as total_receipts'),
                DB::raw('SUM(CAST(receipts.total_fee AS DECIMAL(15,2))) as total_revenue')
            )
            ->join('departments', 'departments.id', '=', 'receipts.centre')
            ->whereNotIn('receipts.centre', [1, 4, 5])
            ->where('receipts.type', 0)
            ->whereDate('receipts.payment_date', $current_date)
            ->groupBy('receipts.centre', 'departments.name', 'departments.code')
            ->orderBy('total_revenue', 'desc')
            ->get();

            // คำนวณยอดรวมทั้งหมด
            $grand_total = $branch_revenue->sum('total_revenue');
            $total_receipts = $branch_revenue->sum('total_receipts');

            return response()->json([
                'success' => true,
                'message' => 'สรุปรายได้รายวันแยกตามสาขาสำเร็จ',
                'data' => [
                    'date' => $current_date,
                    'grand_total' => $grand_total,
                    'total_receipts' => $total_receipts,
                    'branches' => $branch_revenue,
                ],
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8'], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred fetching data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
