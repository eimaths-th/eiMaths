<?php

namespace App\Http\Controllers;

use AddCommissionListInAffiliate;
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
use App\Models\AffiliateCommissionList;
use App\Models\CoursePending;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Detection\MobileDetect;
use Exception;
use Modules\Affiliate\Entities\AffiliateConfiguration;
use PDF;


class OrderController extends Controller
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
                ->join("departments", "contacts.centre", "=", "departments.id")
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
            ->join("contacts", "contacts.id", "=", "orders.cid")
            ->join("departments", "contacts.centre", "=", "departments.id")
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

        // Load the template file from the 'templates' subdirectory
        $template = 'templates.print_order';
        $templates = 'templates.print_social';

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

    // private function update_commmission_list($courses_pending_id, $order_id, $reciept_id)
    // {
    //     // Retrieve the order by ID and update the receipt_id
    //     AffiliateCommissionList::where([
    //         'order_id' => $order_id,
    //         'courses_pending_id' => $courses_pending_id,
    //     ])->update(['reciept_id' => $reciept_id, 'status' => 'paid']); // Correct format
    // }
    // private function update_commmission_list2($payment_to, $order_id, $reciept_id)
    // {
    //     // Retrieve the order by ID and update the receipt_id
    //     AffiliateCommissionList::where([
    //         'order_id' => $order_id,
    //         'payment_to' => $payment_to,
    //     ])->update(['reciept_id' => $reciept_id, 'status' => 'paid']); // Correct format
    // }

    private function sent_ref_to_reciept($ref, $order_id,$courses_pending_id)
    {
        $month = date('m');
        $year = date('Y');
        $user = User::where('referral', $ref)->first();
        $order = Order::find($order_id);
        $aff_config = AffiliateConfiguration::find(3);
        $commission_rate = $aff_config->value / 100;
        AffiliateCommissionList::create([
            'payment_to' => $user->id,
            'courses_pending_id' => $courses_pending_id,
            'order_id' => $order_id,
            'month' => $month,
            'year' => $year,
            'status' => 'pending',
            'commission_amount' => ($order->total_price - ($order->register_fee + $order->access_fee)) * $commission_rate,
        ]);
    }

    private function update_commmission_list($order_id, $receipt_id, $courses_pending_id = null, $payment_to = null)
    {
        $conditions = ['order_id' => $order_id];

        if ($courses_pending_id) {
            $conditions['courses_pending_id'] = $courses_pending_id;
        }

        if ($payment_to) {
            $conditions['payment_to'] = $payment_to;
        }
        // dd($conditions);
        // Use where with an array for conditions
        $testbug = AffiliateCommissionList::where($conditions)->update([
            'reciept_id' => $receipt_id,
            'status' => 'paid',
        ]);
        return $testbug;
    }


    public function update(Request $request, $id)
    {
        //
        // dd($request->all());
        $rules = [
            'order_number' => 'required|string|max:20',
            'cid' => 'required|integer|max:255',
        ];


        $validator =  Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $status = $request->get('status');

        $data = [
            'cid' => $request->get('cid'),
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

        $order = Order::find($id);
        $order->update($data);

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
        if ($status == 1) {
            $receipt = new Receipt();
            $receipt->centre = $order->centre;
            $receipt->cid = $order->cid;
            $receipt->ref = $order->ref;
            $receipt->courses_pending_id = $order->courses_pending_id;
            $receipt->student_no = $order->contact->code;
            $receipt->student_name = $order->contact->name;
            $receipt->receipt_number = ReceiptRunningNumber::generate($order->centre);
            $receipt->payment_date = date("Y-m-d");
            $receipt->oid = $order->id;
            $receipt->payment = $order->payment;
            $receipt->level = $order->contact->level_name;
            $receipt->total_fee = $order->total_price;
            $receipt->start_term = $order->contact->start_term;
            $receipt->save();

            $check = AffiliateCommissionList::where('order_id', $order->id)->get();
            // $check = AffiliateCommissionList::where('order_id', $order->id)->count();
            $c = count($check);

            if ($c == 0 && $order->ref) { 

                // $commission  = $this->sent_ref_to_reciept($order->ref, $receipt->oid, $receipt->courses_pending_id ?? null);
                create_commmission_list($receipt->oid, $receipt->courses_pending_id ?? null, $order->ref);
            }

            if ($order->courses_pending_id) {
                $statusss = 'succ';
                $this->updateWaitingForPayment($statusss, $receipt->courses_pending_id);
            }

            $user_payment_to = User::where('referral', $order->ref)->first();

            if ($order->ref && $order->courses_pending_id) {
                # code...
                $testbug = $this->update_commmission_list($receipt->oid, $receipt->id, $receipt->courses_pending_id, $user_payment_to->id);
                // dd('1', $testbug, 'true');
            } elseif ($order->ref && !$order->courses_pending_id) {

                $this->update_commmission_list($receipt->oid, $receipt->id, null, $user_payment_to->id);
                
            }

            // dd($order->ref, $order->courses_pending_id);

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
                ->where("receipts.id", $receipt->id)
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

            $receipt_detail = new receipt_details();
            $receipt_detail->receipt_id = $receipt->id;
            $receipt_detail->des = 'ค่าคอร์สเรียน ' . $ccname;
            $receipt_detail->unit = '';
            $receipt_detail->quantity = $qty;
            $receipt_detail->price = $maxPrice;
            $receipt_detail->discount = $datas[0]->discount;
            $receipt_detail->tax = 1;
            $receipt_detail->save();

            $receipt_detail = new receipt_details();
            $receipt_detail->receipt_id = $receipt->id;
            $receipt_detail->des = 'ค่าหนังสือ';
            $receipt_detail->unit = '';
            $receipt_detail->quantity = $qty;
            $receipt_detail->price = $maxBPrice;
            $receipt_detail->discount = $datas[0]->discount_book;
            $receipt_detail->tax = 0;
            $receipt_detail->save();

            if ($datas[0]->refund !== "" and $datas[0]->refund !== "0.00") {
                $receipt_detail = new receipt_details();
                $receipt_detail->receipt_id = $receipt->id;
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
                $receipt_detail->receipt_id = $receipt->id;
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
                $receipt_detail->receipt_id = $receipt->id;
                $receipt_detail->des = 'ค่าอุปกรณ์การเรียน';
                $receipt_detail->unit = '';
                $receipt_detail->quantity = 1;
                $receipt_detail->price = $datas[0]->access_fee;
                $receipt_detail->discount = '0.00';
                $receipt_detail->tax = 1;
                $receipt_detail->save();
            };
        }

        return response()->json(['success' => 'Save Data completed', 'status' => $order->status]);
    }


    public function cancle($id)
    {

        $order = Order::find($id);
        $contact = $order->cid;
        if ($order) {
            $order->delete();

            OrderDetail::where('order_id', $id)->delete();
            OrderDetailList::where('order_id', $id)->delete();

            $filestore = FileStore::where([
                ['module', 'orders'],
                ['module_id', $id],
            ])->get();

            if (!$filestore->isEmpty()) {
                foreach ($filestore as $file) {
                    $filePath = public_path('file_store/' . $file->filename);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $file->delete();
                }
            }
        }

        $last_order = Order::where('cid', $contact)
            ->orderBy('id', 'DESC')
            ->first();
        $last_order_detail = OrderDetailList::where('order_id', $last_order->id)
            ->orderBy('id', 'ASC')
            ->get();

        $rowCount = $last_order_detail->count();
        if ($rowCount == 2) {
            $level1 = $last_order_detail[0]->level;
            $term1 = $last_order_detail[0]->term;
            $book1 = $last_order_detail[0]->book;

            $level2 = $last_order_detail[1]->level;
            $term2 = $last_order_detail[1]->term;
            $book2 = $last_order_detail[1]->book;

            $book1i = bookuse::where('name', $book1)->first();
            $level1_id = $book1i->level_id;
            $term1_id = $book1i->term_id;
            $book1_id = $book1i->id;

            $book2i = bookuse::where('name', $book2)->first();
            $level2_id = $book2i->level_id;
            $term2_id = $book2i->term_id;
            $book2_id = $book2i->id;
        } else {
            $level1 = $last_order_detail[0]->level;
            $term1 = $last_order_detail[0]->term;
            $book1 = $last_order_detail[0]->book;

            $level2 = $last_order_detail[0]->level;
            $term2 = $last_order_detail[0]->term;
            $book2 = $last_order_detail[0]->book;

            $book1i = bookuse::where('name', $book1)->first();
            $level1_id = $book1i->level_id;
            $term1_id = $book1i->term_id;
            $book1_id = $book1i->id;

            $level2_id = $book1i->level_id;
            $term2_id = $book1i->term_id;
            $book2_id = $book1i->id;
        }

        $student = Contact::find($contact);

        $data = [
            'order' => $last_order->id,
            'level' => $level1_id,
            'term' => $term1_id,
            'bookuse' => $book1_id,
            'level_name' => $level1,
            'term_name' => $term1,
            'bookuse_name' => $book1,
            'level2' => $level2_id,
            'term2' => $term2_id,
            'bookuse2' => $book2_id,
            'level2_name' => $level2,
            'term2_name' => $term2,
            'bookuse2_name' => $book2,
        ];

        $student->update($data);

        return response()->json(['success' => 'Cancle Order completed']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function confirm(Request $request)
    {
        $id = $request->get('id');
        $order = Order::find($id);
        OrderDetail::where('order_id', $id)->get();

        //invoice
        $invoice = new Invoice();
        $invoice->centre = $order->centre;
        $invoice->cid = $order->cid;
        $invoice->student_no = $order->contact->code;
        $invoice->student_name = $order->contact->name;
        //$invoice->invoice_number = ReceiptRunningNumber::generate($order->centre);
        //$invoice->invoice_date = date("Y-m-d");
        $invoice->oid = $order->id;
        $invoice->level = $order->contact->level_name;
        $invoice->total_fee = $order->total_price;
        $invoice->order_term = 4;
        $invoice->status = 0;
        $invoice->save();

        //receipt
        $receipt = new Receipt();
        $receipt->type = 1;
        $receipt->centre = $order->centre;
        $receipt->cid = $order->cid;
        $receipt->student_no = $order->contact->code;
        $receipt->student_name = $order->contact->name;
        //$receipt->receipt_number = ReceiptRunningNumber::generate($order->centre);
        //$receipt->payment_date = date("Y-m-d");
        $receipt->oid = $order->id;
        $receipt->level = $order->contact->level_name;
        $receipt->total_fee = $order->total_price;
        $receipt->start_term = $order->contact->start_term;
        $invoice->status = 0;
        $receipt->save();

        $order->status = 2;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Confirm Order successfully']);
    }
}
