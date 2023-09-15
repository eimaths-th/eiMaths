<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceRunningNumber;
use App\Models\Order;
use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\term;
use App\Models\Sterm;
use App\Models\FileStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Interfaces\MoneyConvertServiceInterface;
use PDF;

class InvoiceController extends Controller
{
    protected $MoneyConvertService;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(MoneyConvertServiceInterface $MoneyConvertService)
    {
        $this->middleware('permission:invoice-list|invoice-show|invoice-create|invoice-edit|invoice-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:invoice-show', ['only' => ['show']]);
        $this->middleware('permission:invoice-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:invoice-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:invoice-delete', ['only' => ['destroy']]);

        $this->MoneyConvertService = $MoneyConvertService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            //sleep(2);
            $query = Invoice::select(
                "invoices.id as id",
                "invoices.invoice_number as invoice_number",
                "invoices.total_fee as total_fee",
                "invoices.invoice_date as invoice_date",
                "invoices.level as level",
                "invoices.status as invoice_status",
                "invoices.order_term as order_term",
                "contacts.name as contact_name",
                "contacts.code as contact_code",
                "contacts.start_term as start_term",
                "departments.name as centre",
            )

                ->join("contacts", "contacts.id", "=", "invoices.cid")
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->where('invoices.status', 1);
            if (!Gate::allows('all-centre')) {
                $query->where('invoices.centre', Auth::user()->department->id);
            }

            if (!empty($request->get('sdate'))) {
                //dd($request->get('sdate'));
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('invoices.invoice_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    }
                }
            }
            $datas = $query->orderBy("invoices.id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::allows('invoice-show')) {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print" id="getPrintData" data-id="' . $row->id . '"><i class="fa fa-print"></i> Print</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-print"></i> Print</button> ';
                    }
                    if (Gate::allows('invoice-show')) {
                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-share" id="getShareData" data-id="' . $row->id . '"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger btn-share disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa-solid fa-file-pdf"></i> PDF</button> ';
                    }
                    return "<div class='col-sm-2 col-md-2 col-xl-2'>" . $html . "</div>";
                })->rawColumns(['checkbox', 'action', 'total_fee'])->toJson();
        }

        return view('invoices.index');
    }

    public function pending(Request $request)
    {
        if ($request->ajax()) {
            $query = Invoice::select(
                "invoices.id as id",
                "invoices.created_at as created_at",
                "invoices.total_fee as total_fee",
                "invoices.invoice_date as invoice_date",
                "invoices.level as level",
                "invoices.status as invoice_status",
                "contacts.name as contact_name",
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

                ->join("contacts", "contacts.id", "=", "invoices.cid")
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("orders", "orders.id", "=", "invoices.oid")
                ->leftJoin("order_details as first_detail", function ($join) {
                    $join->on("first_detail.order_id", "=", "orders.id")
                        ->whereRaw("first_detail.id = (SELECT MIN(id) FROM order_details WHERE order_id = orders.id)");
                })
                ->join("bookuses", "bookuses.id", "=", "first_detail.product_id")
                ->where('invoices.status', 0);
            if (!Gate::allows('all-centre')) {
                $query->where('invoices.centre', Auth::user()->department->id);
            }


            if (!empty($request->get('sdate'))) {
                //dd($request->get('sdate'));
                $dateRange = $request->input('sdate');
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('invoices.created_at', [$startDate, $endDate]);
                    }
                }
            }
            $datas = $query->orderBy("invoices.id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('created_at', function (Invoice $datas) {
                    return date('Y-m-d', strtotime($datas->created_at));
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::allows('order-edit')) {

                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-generate" data-book="' . $row->next_book_name . '" data-price="' . $row->next_book_price . '" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Generate Invoice</button> ';
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



        return view('invoices.pending');
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
    public function show($id, $social = 0)
    {
        // Get the data from the database query
        $datas = Invoice::select(
            "invoices.*",
            "invoices.invoice_number as doc_number",
            "contacts.name as cname",
            "contacts.telephone as ctelephone",
            "contacts.code as ccode",
            "invoices.level as book",
            "departments.name as centre_name",
            "invoices.total_fee as totalp",
            "invoices.total_fee as price"

        )
            ->join("contacts", "contacts.id", "=", "invoices.cid")
            ->join("departments", "contacts.centre", "=", "departments.id")
            ->where("invoices.id", $id)
            ->get();


        if ($datas->isEmpty()) {
            return redirect()->back()->with('error', 'Receipt data not found.');
        }

        // Load the template file from the 'templates' subdirectory
        $template = 'templates.print_invoice';
        $templates = 'templates.print_social';

        $ctype = "";
        $price_text = $this->MoneyConvertService->baht_text($datas[0]->total_fee);
        $price_texten = $this->MoneyConvertService->convertNumber($datas[0]->total_fee);

        $formattedDate = $datas[0]->created_at->format('Y-m-d');

        // Data to pass to the view

        $customItem = [
            'pname' => $datas[0]->book,
            'quantity' => 1,
            'price' => $datas[0]->price,
            // Add other properties as needed
        ];

        $data = [
            'ctype' => $ctype,
            'price_text' => $price_text,
            'price_texten' => $price_texten,
            'etype' => "ค่าคอร์ส / Term Fee",
            'date' => $formattedDate,
            'data' => $datas->first(),
            'items' => compact('customItem'),
        ];


        if ($social == 1) {
            // Render the view with the data and get the HTML content
            $htmlContents = View::make($templates, $data)->render();

            // Generate the PDF
            $pdf = PDF::loadHTML($htmlContents);
            //$pdf->getDomPDF()->getFontMetrics()->setFont('YourThaiFont.ttf', 'YourThaiFont');

            // Save the PDF to a temporary file path
            $pdfPath = public_path('file_store/' . $datas[0]->order_number . '.pdf');
            //dd($pdfPath);
            $pdf->save($pdfPath);

            // Define the file name for the downloaded PDF
            $pdfFileName = $datas[0]->order_number . '.pdf';

            // Download the PDF file
            return response()->download($pdfPath, $pdfFileName, ['Content-Type' => 'application/pdf']);
        } else {
            // Render the view with the data and get the HTML content
            $htmlContent = View::make($template, $data)->render();
            //return new Response($htmlContent, 200, ['Content-Type' => 'text/html']);
            return response()->json([
                'html' =>  $htmlContent
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function generate(Request $request)
    {
        $id = $request->get('id');
        $invoice = Invoice::find($id);

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
        $invoice->invoice_number = InvoiceRunningNumber::generate($invoice->centre);
        $invoice->level = $request->get('book');
        $invoice->total_fee = $request->get('price');
        $invoice->invoice_date = date("Y-m-d");
        $invoice->status = 1;
        $invoice->save();


        return response()->json(['success' => true, 'message' => 'Generate Invoice successfully']);
    }
}
