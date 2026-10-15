<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReceiptRunningNumber;
use App\Models\OrderDetail;
use App\Models\OrderDetailList;
use App\Models\Contact;
use App\Models\FileUpload;
use App\Models\FileStore;
use App\Models\Department;
use App\Models\Parameter;
use App\Models\level;
use App\Models\term;
use App\Models\Sterm;
use App\Models\bookuse;
use App\Models\Receipt;
use App\Models\receipt_details;
use App\Models\Invoice;

//use App\Models\LogActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\MoneyConvertServiceInterface;
use App\Models\CoursePending;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Detection\MobileDetect;
use Exception;
use PDF;
use App\Domain\Affiliate\Events\AffiliatePaymentConfirmed;
use App\Domain\Financial\Services\FinancialSourceGuard;
use App\Domain\Financial\Services\ReceiptDetailBuilder;
use App\Domain\Financial\ValueObjects\ReceiptDetailDraft;
use App\Domain\CreditNote\Enums\ReceiptLineType;
use App\Domain\CreditNote\Services\ReceiptLineMetadataResolver;


class OrderController extends Controller
{

    protected $MoneyConvertService;
    protected FinancialSourceGuard $financial_source_guard;
    protected ReceiptDetailBuilder $receipt_detail_builder;
    protected ReceiptLineMetadataResolver $receipt_line_metadata_resolver;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(
        MoneyConvertServiceInterface $MoneyConvertService,
        FinancialSourceGuard $financial_source_guard,
        ReceiptDetailBuilder $receipt_detail_builder,
        ReceiptLineMetadataResolver $receipt_line_metadata_resolver,
    )
    {
        $this->middleware('permission:order-list|order-show|order-create|order-edit|order-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:order-show', ['only' => ['show']]);
        $this->middleware('permission:order-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:order-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:order-delete', ['only' => ['destroy']]);

        $this->MoneyConvertService = $MoneyConvertService;
        $this->financial_source_guard = $financial_source_guard;
        $this->receipt_detail_builder = $receipt_detail_builder;
        $this->receipt_line_metadata_resolver = $receipt_line_metadata_resolver;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            //sleep(2);
            $para = Parameter::orderBy("id", "asc")->get();
            $detect = new MobileDetect();
            $query = Order::select(
                "orders.id as id",
                "orders.order_number as order_number",
                "orders.total_price as total_price",
                "orders.created_at as created_at",
                "orders.status as order_status",
                "contacts.name as contact_name",
                "contacts.code as contact_code",
                "departments.name as centre",
                "bookuses.name as product_name",
                "bookuses.unit as product_unit",
                "first_detail.quantity as amount"
            )
                ->join("contacts", "contacts.id", "=", "orders.cid")
                ->join("departments", "orders.centre", "=", "departments.id")
                ->leftJoin("order_details as first_detail", function ($join) {
                    $join->on("first_detail.order_id", "=", "orders.id")
                        ->whereRaw("first_detail.id = (SELECT MIN(id) FROM order_details WHERE order_id = orders.id)");
                })
                ->join("bookuses", "bookuses.id", "=", "first_detail.product_id")
                ->where('orders.status', 1);

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
            $datas = $query->orderBy("orders.id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('created_at', function (Order $datas) {
                    return date('Y-m-d H:i:s', strtotime($datas->created_at));
                })
                ->addColumn('book_qty', function () {
                    return 1;
                })
                ->addColumn('bag_qty', function () {
                    return 1;
                })
                ->addColumn('bag_price', function () use ($para) {
                    return $para[3]->price;
                })
                ->addColumn('more', function () {
                    return '';
                })
                ->addColumn('action', function ($row) use ($detect) {
                    if ($detect->isMobile()) {
                        $brt = "<br><br>";
                    } else {
                        $brt = '';
                    }
                    $html = '';

                    /*  if ($row->order_status == 1) {
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
                    } */
                    if (Gate::allows('order-edit')) {

                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-confirm" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Confirm
                        Order</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Confirm
                        Order</button> ';
                    }
                    /*if (Gate::allows('order-delete')) {
                        $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-danger disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-trash"></i> Delete</button> ';
                    }
                      if (Gate::allows('log-list')) {
                        $html .= $brt.'<button type="button" class="btn btn-sm btn-success btn-log" id="getLogData" data-id="' . $row->id . '"><i class="fas fa-clipboard"></i> Log</a> ';
                    } else {
                        $html .= $brt.'<button type="button" class="btn btn-sm btn-success disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fas fa-clipboard"></i> Log</a> ';
                    } */
                    return "<div class='col-sm-2 col-md-2 col-xl-2'>" . $html . "</div>";
                })->rawColumns(['checkbox', 'action'])->toJson();
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

        return view('orders.index')->with(['product' => $product])
            ->with(['contact' => $contact])
            ->with(['order_type' => $customCollection])
            ->with(['centre' => $centre])
            ->with(['level' => $level])
            ->with(['term' => $term])
            ->with(['sterm' => $sterm]);
    }



    /**
     * Display the specified resource.
     */
    public function show($id, $social = 0)
    {
        // Get the data from the database query
        $datas = Order::select(
            "orders.*",
            "orders.order_number as doc_number",
            "contacts.name as cname",
            "contacts.telephone as ctelephone",
            "contacts.code as ccode",
            "departments.name as centre_name",

        )
            ->leftJoin('contacts', function ($join) {
                $join->on('contacts.id', '=', 'orders.cid')->on('contacts.centre', '=', 'orders.centre');
            })
            ->join("departments", "orders.centre", "=", "departments.id")
            ->where("orders.id", $id)
            ->get();

        //dd($datas);

        $datasd = OrderDetail::select(
            "order_details.*",
        )->where('order_id', $id)
            ->orderBy("order_details.id", "asc")
            ->get();

        //dd($datasd);

        $datasf = $this->getfileattprint($id);

        // Check if the data exists before proceeding
        if ($datas->isEmpty()) {
            return redirect()->back()->with('error', 'Order data not found.');
        }

        $order_data = $datas->first();
        if (!filled($order_data->cname) || !filled($order_data->ccode)) {
            $issued_identity = Receipt::query()->where('oid', $order_data->id)
                ->where('centre', $order_data->centre)->where('cid', $order_data->cid)
                ->orderBy('id')->first(['student_name', 'student_no']);
            if (!filled($order_data->cname)) {
                $order_data->cname = filled($issued_identity?->student_name)
                    ? $issued_identity->student_name : __('financial-documents.student_unavailable');
            }
            if (!filled($order_data->ccode)) {
                $order_data->ccode = $issued_identity?->student_no;
            }
        }

        // Load the template file from the 'templates' subdirectory
        $template = 'templates.print_order';

        $ctype = "";
        $price_text = $this->MoneyConvertService->baht_text($datas[0]->total_price);
        $price_texten = $this->MoneyConvertService->convertNumber($datas[0]->total_price);

        $formattedDate = $datas[0]->created_at->format('Y-m-d');
        // Data to pass to the view
        $data = [
            'ctype' => $ctype,
            'price_text' => $price_text,
            'price_texten' => $price_texten,
            'etype' => "ค่าสมัครเรียน / Application Fee",
            'date' => $formattedDate,
            'data' => $datas->first(),
            'items' => $datasd,
            'file_att' => $datasf,
        ];


        if ($social == 1) {
            // Render the view with the data and get the HTML content
            $htmlContents = View::make($template, $data)->render();

            // Generate the PDF
            $pdf = PDF::loadHTML($htmlContents);
            //$pdf->getDomPDF()->getFontMetrics()->setFont('YourThaiFont.ttf', 'YourThaiFont');

            // Return bytes directly so duplicate legacy numbers cannot overwrite shared files.
            $pdf_file_name = $datas[0]->order_number . '.pdf';
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => \Symfony\Component\HttpFoundation\HeaderUtils::makeDisposition(
                    'attachment', $pdf_file_name, str_replace('%', '', \Illuminate\Support\Str::ascii($pdf_file_name)),
                ),
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

    /**
     * Display the specified resource.
     */
    public function log($id)
    {
        $data = LogActivity::select(
            "*",
            "log_activities.updated_at as updated_at",
            "users.name as uname",
        )
            ->where('module', 'order')
            ->where('module_id', $id)
            ->join('users', 'users.id', '=', 'log_activities.user_id')
            ->orderBy("log_activities.id", "desc")
            ->take(10)
            ->get();

        $html_table = "";
        $i = 1;

        if ($data->isEmpty()) {
            $html_table .= '<tr>
                <td colspan="5" align="center">ยังไม่มีข้อมูลการแก้ไข</td>
                </tr>';
        } else {
            foreach ($data as $key) {
                $html_table .= '<tr>
                    <td>' . $i . '</td>
                    <td>' . $key->updated_at . '</td>
                    <td>' . $key->subject . '</td>
                    <td>' . $key->uname . '</td>
                    <td>' . $key->datas . '</td>
                    </tr>';

                $i++;
            }
        }

        $html = '
        <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th style="width: 10px">#</th>
                <th>วันที่ทำรายการ</th>
                <th>การกระทำ</th>
                <th>โดย</th>
                <th>ข้อมูล</th>
            </tr>
        </thead>
        <tbody>'
            . $html_table . '
        </tbody>
       </table>';

        return response()->json([
            'html' => $html
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Order::find($id);
        /* $datas = OrderDetail::select(
            "order_details.*",
            "levels.id as level_id",
            "terms.id as term_id",
        )
            ->join('bookuses', 'bookuses.id', '=', 'order_details.product_id')
            ->join("terms", "terms.id", "=", "bookuses.term_id")
            ->join("levels", "levels.id", "=", "bookuses.level_id")
            ->where('order_id', $id)
            ->orderBy("order_details.id", "asc")
            ->get(); */

        $para = Parameter::orderBy("id", "asc")->get();

        //discount
        $discount = !empty($data->discount) ? $data->discount : 0.00;
        $discount_book = !empty($data->discount_book) ? $data->discount_book : 0.00;
        $refund = !empty($data->refund) ? $data->refund : $para[0]->price;
        $register_fee = !empty($data->register_fee) ? $data->register_fee : $para[1]->price;
        $access_fee = !empty($data->access_fee) ? $data->access_fee : $para[2]->price;

        $data_html = '';
        $etotal = 0;

        $invoice_number = ReceiptRunningNumber::pre_generate($data->centre);

        /* $bookq = bookuse::find($datas[0]->product_id);
        $levelq = Level::find($datas[0]->level_id);
        $termq = Term::find($datas[0]->term_id);

        if ($bookq->type == 1) {
            $mprice = $levelq->price;
            $mbprice =  $levelq->book_price;
            $fprice = $levelq->price;
        } elseif ($bookq->type == 2) {
            $mprice = $levelq->half_price;
            $mbprice =  $levelq->book_price;
            $fprice = $levelq->half_price;
        }

        if (!empty($datas[1]->level_id)) {

            $bookq2 = bookuse::find($datas[1]->product_id);
            $levelq2 = Level::find($datas[1]->level_id);
            $termq2 = Term::find($datas[1]->term_id);

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
                    } else {
                        $sbook = 1;
                    }

                    $books = bookuse::select(
                        "bookuses.*",
                        "levels.name as level_name",
                        "levels.price as price",
                        "levels.book_price as book_price",
                        "terms.name as term_name",
                    )->where('type', '=', $sbook)
                        ->join("terms", "terms.id", "=", "bookuses.term_id")
                        ->join("levels", "levels.id", "=", "bookuses.level_id")
                        ->where('bookuses.level_id', '=', $i)
                        ->where('bookuses.term_id', '=', $j)
                        ->get();

                    $lprice = $books[0]->price;
                    $bprice = $books[0]->book_price;
                    $bookss[] = $books;

                    $mprice = $mprice + $lprice;
                    $mbprice = $mbprice + $bprice;
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
                "levels.name as level_name",
                "levels.price as price",
                "levels.book_price as book_price",
                "terms.name as term_name",
            )
                ->join("terms", "terms.id", "=", "bookuses.term_id")
                ->join("levels", "levels.id", "=", "bookuses.level_id")
                ->where('bookuses.level_id', '=', $datas[0]->level_id)
                ->where('bookuses.term_id', '=', $datas[0]->term_id)
                ->get();
            $bookss[] = $books;
        }

        //dd($bookss); */
        $mprice = 0;
        $mbprice = 0;

        $datas_list = OrderDetailList::select(
            "order_detail_lists.*",
        )
            ->where('order_id', $id)
            ->orderBy("id", "asc")
            ->get();

        //  dd($datas_list);


        foreach ($datas_list as $dresult) {
            $mprice = $mprice + $dresult->price;
            $mbprice = $mbprice + $dresult->bprice;
            $etotal = 1 * $dresult->price + $dresult->bprice;
            $data_html .= '<tr class="firstTr">
            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                <input type="text"  id="level" name="level[]" class="form-control has-feedback-left" value="' . $dresult->level . '" readonly>
                    <div id="elot_price" class="text-success"></div>
                    <div id="elot_error" class="text-danger"></div>
                </div>
            </td>
            <td width="10%">
            <div class="col-md-12 col-sm-12 col-xs-12">
            <input type="text"  id="level" name="level[]" class="form-control has-feedback-left" value="' . $dresult->term . '" readonly>
                <div id="elot_price" class="text-success"></div>
                <div id="elot_error" class="text-danger"></div>
            </div>
            </td>
            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                <input type="text"  id="level" name="level[]" class="form-control has-feedback-left" value="' . $dresult->book . '" readonly>
                    <div id="elot_price" class="text-success"></div>
                    <div id="elot_error" class="text-danger"></div>
                </div>
            </td>

            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="number" step="0.50" id="eprice" name="eprice[]" class="auto_decimal form-control has-feedback-left" value="' . number_format($dresult->price, 2, '.', '') . '" readonly>
                </div>
            </td>
            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="number" step="0.50" id="eprice" name="eprice[]" class="auto_decimal form-control has-feedback-left" value="' . number_format($dresult->bprice, 2, '.', '') . '" readonly>
                </div>
            </td>
            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="number" step="0.50" id="etotal" name="etotal[]" class="auto_decimal form-control has-feedback-left" value="' . number_format($etotal, 2, '.', '') . '" readonly>
                </div>
            </td>
        </tr>';
        }

        $img = $this->getfileatt($id);

        $data->termfee = number_format($mprice, 2, '.', '');
        $data->bookfee = number_format($mbprice, 2, '.', '');

        $total_price = (($data->termfee + $data->bookfee + $refund + $register_fee + $access_fee)) - ($discount + $discount_book);

        return response()->json([
            'data' => $data,
            'affiliate_financial_locked' => $this->financial_source_guard->IsOrderLocked($data),
            //'datas' => $datas,
            'table_html' => $data_html,
            'imgs' => $img,
            'receipt_number' => $invoice_number,
            'para' => $para,
            'old_price' => number_format($mprice, 2, '.', ''),
            'old_book' => number_format($mbprice, 2, '.', ''),
            'total_price' => number_format($total_price, 2, '.', '')
        ]);
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

    /**
     * Update the specified resource in storage.
     */

    public function updateWaitingForPayment($status, $id)
    {
        // dd($status, $id);
        $data = CoursePending::find($id);
        if ($status == 'succ') {
            # code...
            $data->update(['status' => 4]);
        }
    }

    public function update(Request $request, $id)
    {
        $order = Order::with('contact')->find($id);

        if (!$order || !$order->contact) {
            return response()->json(['errors' => ['Order or student data not found.']], 404);
        }

        if (!Gate::allows('all-centre') && (int) $order->centre !== (int) Auth::user()->department->id) {
            abort(403);
        }

        $rules = [
            'order_number' => 'required|string|max:20',
            'cid' => 'required|integer|exists:contacts,id',
        ];


        $validator =  Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $status = $request->get('status');

        $data = [
            // The student is fixed by the existing order. Do not trust a client-supplied
            // student id to change the owner while a receipt is being created.
            'cid' => $order->cid,
            'ref' => $request->get('ref'),
            'courses_pending_id' => $request->get('courses_pending_id'),
            'total_price' => $request->get('total_cost'),
            'detail' => $request->get('detail'),
            'refund' => $request->get('refund'),
            'register_fee' => $request->get('register_fee'),
            'access_fee' => $request->get('access_fee'),
            'orther_fee' => $request->get('orther_fee'),
            'discount' => $request->get('discount'),
            'payment' => $request->get('payment'),
            'discount_book' => $request->get('discount_book'),
            'status' => $status,
            'updated_by' => Auth::id()
        ];

        $this->financial_source_guard->AssertOrderUpdateAllowed($order, $data);
        $receipt = DB::transaction(function () use ($order, $data, $status, $request) {
            $order->update($data);
            if ((int) $status !== 1) {
                return null;
            }

            $new_receipt = new Receipt();
            $new_receipt->centre = $order->centre;
            $new_receipt->cid = $order->cid;
            $new_receipt->ref = $order->ref;
            $new_receipt->courses_pending_id = $order->courses_pending_id;
            $new_receipt->student_no = $order->contact->code;
            $new_receipt->student_name = $order->contact->name;
            $new_receipt->billing_details = $order->contact->defaultBillingDetails();
            $new_receipt->receipt_number = ReceiptRunningNumber::generate($order->centre);
            $new_receipt->payment_date = date("Y-m-d");
            $new_receipt->oid = $order->id;
            $new_receipt->payment = $order->payment;
            $new_receipt->level = $order->contact->level_name;
            $new_receipt->total_fee = $order->total_price;
            $new_receipt->start_term = $order->contact->start_term;
            $new_receipt->save();
            event(new AffiliatePaymentConfirmed($new_receipt->id, now(), (string) ($request->header('X-Correlation-ID') ?: \Illuminate\Support\Str::uuid())));

            return $new_receipt;
        });

        /* OrderDetail::where('order_id', $id)->delete();

                $amounts = $request->input('amount');
                $prices = $request->input('price');
                $sids = $request->input('sid');

                if (is_array($amounts) && is_array($prices) && count($amounts) === count($prices)) {
                foreach ($amounts as $index => $amount) {
                if (isset($sids[$index])) { // Check if the index exists in $sids array
                    $priceq = bookuse::find($sids[$index]);
                    $orderDetail = new OrderDetail();
                    $orderDetail->order_id = $id;
                    $orderDetail->product_id = $sids[$index];
                    $orderDetail->pname = $priceq->name;
                    $orderDetail->punit = $priceq->unit;
                    $orderDetail->quantity = $amount[$index];
                    $orderDetail->price = $prices[$index];
                    $orderDetail->save();
                }
                }
                }
            */
        //file att

        $filesa = $request->get('img');

        if (!empty($filesa)) {
            foreach ($filesa as $filea) {
                //echo $filea;
                $oldfile = FileUpload::where('oldname', $filea)->get();
                $filestore = new FileStore();
                $filestore->module = 'orders';
                $filestore->module_id = $id;
                $filestore->filename = $oldfile[0]->filename;
                $filestore->save();

                File::move(public_path() . '/file_upload/' . $oldfile[0]->filename, public_path() . '/file_store/' . $oldfile[0]->filename);
                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
        }

        //receipt
        if ($status == 1 && $receipt !== null) {
            if ($order->courses_pending_id) {
                $statusss = 'succ';
                $this->updateWaitingForPayment($statusss, $receipt->courses_pending_id);
            }

            $receipt_detail_drafts = $this->receipt_detail_builder->BuildForOrder($order->fresh());
            $this->PersistReceiptDetailDrafts($receipt->id, $receipt_detail_drafts);
        }

        return response()->json(['success' => 'Save Data completed', 'status' => $order->status]);
    }

    private function PersistReceiptDetailDrafts(int $receipt_id, Collection $receipt_detail_drafts): void
    {
        foreach ($receipt_detail_drafts as $receipt_detail_draft) {
            if (!$receipt_detail_draft instanceof ReceiptDetailDraft) {
                throw new \UnexpectedValueException('Receipt detail draft has an invalid type.');
            }

            $receipt_detail = new receipt_details();
            $receipt_detail->receipt_id = $receipt_id;
            $receipt_detail->des = $receipt_detail_draft->description;
            $receipt_detail->unit = $receipt_detail_draft->unit;
            $receipt_detail->quantity = $receipt_detail_draft->quantity;
            $receipt_detail->price = $receipt_detail_draft->UnitPriceDecimal();
            $receipt_detail->discount = $receipt_detail_draft->DiscountDecimal();
            $receipt_detail->tax = $receipt_detail_draft->legacy_tax;
            $this->ApplyReceiptLineMetadata(
                $receipt_detail,
                $receipt_detail_draft->line_type,
                $receipt_detail_draft->legacy_tax,
            );
            $receipt_detail->save();
        }
    }


    public function cancle($id)
    {
        DB::transaction(fn () => $this->cancelOrder((int) $id));
        return response()->json(['success' => 'Cancel Order completed']);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['id' => 'required|integer']);
        return $this->cancle($data['id']);
    }

    public function destroy_all(Request $request)
    {
        $data = $request->validate(['table_records' => 'required|array|min:1|max:500', 'table_records.*' => 'integer|distinct']);
        DB::transaction(function () use ($data) {
            foreach ($data['table_records'] as $id) {
                $this->cancelOrder((int) $id);
            }
        });
        return $request->expectsJson() ? response()->json(['success' => true]) : back()->with('success', 'ยกเลิกใบสั่งเรียนที่เลือกแล้ว');
    }

    private function cancelOrder(int $id): void
    {
        $order = Order::query()->lockForUpdate()->findOrFail($id);
        $this->financial_source_guard->AssertOrderCancellationAllowed($order);
        $student = Contact::query()->lockForUpdate()->find($order->cid);
        // Delete children while their parent is still visible to the branch scope.
        OrderDetail::where('order_id', $id)->get()->each->delete();
        OrderDetailList::where('order_id', $id)->get()->each->delete();
        foreach (FileStore::where('module', 'orders')->where('module_id', $id)->get() as $file) {
            $filename = basename($file->filename);
            $file->delete();
            DB::afterCommit(function () use ($filename) {
                $path = public_path('file_store/'.$filename);
                if (is_file($path)) { unlink($path); }
            });
        }
        $order->delete();
        if ($student && (int) $student->order === $id) {
            $previous = Order::where('cid', $student->id)->where('centre', $student->centre)->latest('id')->first();
            $student->order = $previous?->id ?? 0;
            $details = $previous ? OrderDetailList::where('order_id', $previous->id)->orderBy('id')->get() : collect();
            foreach (['' => $details->first(), '2' => $details->last()] as $suffix => $detail) {
                $book = $detail ? bookuse::where('name', $detail->book)->first() : null;
                foreach (['level' => $book?->level_id, 'term' => $book?->term_id, 'bookuse' => $book?->id,
                    'level_name' => $detail?->level, 'term_name' => $detail?->term, 'bookuse_name' => $detail?->book] as $key => $field) {
                    $column = str_ends_with($key, '_name') ? substr($key, 0, -5).$suffix.'_name' : $key.$suffix;
                    $student->setAttribute($column, str_ends_with($key, '_name') ? $field : ($field ?? 0));
                }
            }
            $student->save();
        }
    }

    public function deleteImg($id, $id2)
    {
        return app(ReceiptController::class)->deleteImg($id, $id2);
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate(['id' => 'required|integer|min:1']);

        return DB::transaction(function () use ($validated) {
            $order = Order::query()->lockForUpdate()->findOrFail($validated['id']);
            if ((int) $order->status !== 1 || Invoice::where('oid', $order->id)->exists()
                || Receipt::where('oid', $order->id)->where('type', 1)->exists()) {
                return response()->json(['success' => false, 'message' => 'Order has already been confirmed.'], 409);
            }

            $contact = $order->contact;
            if (!$contact) {
                return response()->json(['success' => false, 'message' => 'Student not found.'], 422);
            }

            $invoice = new Invoice();
            $invoice->centre = $order->centre;
            $invoice->cid = $order->cid;
            $invoice->student_no = $contact->code;
            $invoice->student_name = $contact->name;
            $invoice->oid = $order->id;
            $invoice->level = $contact->level_name;
            $invoice->total_fee = $order->total_price;
            $invoice->order_term = 4;
            $invoice->status = 0;
            $invoice->save();

            $receipt = new Receipt();
            $receipt->type = 1;
            $receipt->centre = $order->centre;
            $receipt->cid = $order->cid;
            $receipt->student_no = $contact->code;
            $receipt->student_name = $contact->name;
            $receipt->billing_details = $contact->defaultBillingDetails();
            $receipt->oid = $order->id;
            $receipt->level = $contact->level_name;
            $receipt->total_fee = $order->total_price;
            $receipt->start_term = $contact->start_term;
            $receipt->status = 0;
            $receipt->save();

            $order->status = 2;
            $order->save();

            return response()->json(['success' => true, 'message' => 'Confirm Order successfully']);
        });
    }

    private function ApplyReceiptLineMetadata(
        receipt_details $receipt_detail,
        ReceiptLineType $line_type,
        int $legacy_tax,
    ): void {
        if (!Schema::hasColumn('receipt_details', 'line_type')
            || !Schema::hasColumn('receipt_details', 'tax_treatment')
            || !Schema::hasColumn('receipt_details', 'vat_rate_basis_points')) {
            return;
        }
        $metadata = $this->receipt_line_metadata_resolver->ResolveGenerated($line_type, $legacy_tax);
        $receipt_detail->line_type = $metadata->line_type;
        $receipt_detail->tax_treatment = $metadata->tax_treatment;
        $receipt_detail->vat_rate_basis_points = $metadata->vat_rate_basis_points;
    }
}
