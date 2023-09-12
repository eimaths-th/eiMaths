<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Contact;
use App\Models\Department;
use App\Models\level;
use App\Models\term;
use App\Models\Sterm;
use App\Models\FileStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Interfaces\MoneyConvertServiceInterface;
use PDF;

class ReceiptController extends Controller
{

    protected $MoneyConvertService;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(MoneyConvertServiceInterface $MoneyConvertService)
    {
        $this->middleware('permission:order-list|order-show|order-create|order-edit|order-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:order-show', ['only' => ['show']]);
        $this->middleware('permission:order-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:order-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:order-delete', ['only' => ['destroy']]);

        $this->MoneyConvertService = $MoneyConvertService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            //sleep(2);
            $query = Receipt::select(
                "receipts.id as id",
                "receipts.receipt_number as receipt_number",
                "receipts.total_fee as total_fee",
                "receipts.payment_date as payment_date",
                "receipts.level as level",
                "receipts.status as order_status",
                "contacts.name as contact_name",
                "contacts.code as contact_code",
                "contacts.start_term as start_term",
                "departments.name as centre",
            )
                ->join("contacts", "contacts.id", "=", "receipts.cid")
                ->join("departments", "contacts.centre", "=", "departments.id");
            //->where('orders.status', 0);

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
            $datas = $query->orderBy("receipts.id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('more', function ($row) {
                    return '';
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
                "contacts.code as contact_code",
                "contacts.start_term as start_term",
                "departments.name as centre",
                "products.name as product_name",
                "products.unit as product_unit",
                "first_detail.quantity as amount"
            )
                ->join("contacts", "contacts.id", "=", "orders.cid")
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->leftJoin("order_details as first_detail", function ($join) {
                    $join->on("first_detail.order_id", "=", "orders.id")
                        ->whereRaw("first_detail.id = (SELECT MIN(id) FROM order_details WHERE order_id = orders.id)");
                })
                ->join("products", "products.id", "=", "first_detail.product_id")
                ->where('orders.status', 0);

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
            $datas = $query->orderBy("orders.id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('created_at', function (Order $datas) {
                    return date('Y-m-d H:i:s', strtotime($datas->created_at));
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
        $product = Product::orderBy("id", "asc")->get();
        /* where([['discontinued', '0']]) */
        $contact = Contact::where([['centre', Auth::user()->department->id]])
            ->orderBy("id", "asc")->get();

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
            "orders.promotion as promotion",
            "first_detail.price as price"

        )
            ->join("contacts", "contacts.id", "=", "receipts.cid")
            ->join("orders", "orders.id", "=", "receipts.oid")
            ->leftJoin("order_details as first_detail", function ($join) {
                $join->on("first_detail.order_id", "=", "orders.id")
                    ->whereRaw("first_detail.id = (SELECT MIN(id) FROM order_details WHERE order_id = orders.id)");
            })
            ->join("departments", "contacts.centre", "=", "departments.id")
            ->where("receipts.id", $id)
            ->get();

        //dd($datas);


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
            'etype' => "ค่าสมัครเรียน / Application Fee",
            'date' => $formattedDate,
            'data' => $datas->first(),
            'items' => compact('customItem'),
            'file_att' => $datasf,
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

    public function getfileattprint($id)
    {
        $filestore = FileStore::where([['module', 'orders']])
            ->where([['module_id', $id]])
            ->orderBy("id", "asc")->get();

        $img = "";
        if (!$filestore->isEmpty()) {
            foreach ($filestore as $pics) {
                $imgf = url('/') . '/file_store/' . $pics->filename;
                $img .= "<img src=\"" . $imgf . "\" height=\"200\">&nbsp;&nbsp;&nbsp;";
            }
        } else {
            $img = "";
        }

        return $img;
    }
}
