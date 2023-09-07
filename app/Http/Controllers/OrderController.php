<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderRunningNumber;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Contact;
use App\Models\FileUpload;
use App\Models\FileStore;
//use App\Models\LogActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\MoneyConvertServiceInterface;
use App\OrderDetail as AppOrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Detection\MobileDetect;


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
            $detect = new MobileDetect();
            $datas = Order::select(
                "orders.id as id",
                "orders.order_number as order_number",
                "orders.total_price as total_price",
                "orders.created_at as created_at",
                "contacts.name as contact_name",
                "contacts.code as contact_code",
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
                ->orderBy("orders.id", "desc")
                ->get();
            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->editColumn('amount', function (Order $datas) {

                    $amount_text = number_format($datas->amount, 2, '.', ',') . ' ' . $datas->unit;
                    return $amount_text;
                })
                ->editColumn('cost', function (Order $datas) {
                    return number_format($datas->cost, 2, '.', ',') . " บาท";
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
                ->addColumn('action', function ($row) use ($detect) {
                    if ($detect->isMobile()) {
                        $brt = "<br><br>";
                    } else {
                        $brt = '';
                    }
                    if (Gate::allows('order-show')) {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print" id="getPrintData" data-id="' . $row->id . '"><i class="fa fa-print"></i> Print</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-success btn-print disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-print"></i> Print</button> ';
                    }
                    if (Gate::allows('order-edit')) {
                        $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html .= '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
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
                })->rawColumns(['checkbox', 'action', 'total_cost'])->toJson();
        }


        $customData = [
            [
                'id' => 1,
                'name' => 'Application Fee',
            ],
        ];
        $customCollection = collect($customData);
        $product = Product::orderBy("id", "asc")->get();
        $contact = Contact::where([['discontinued', '0']])
            ->where([['centre', Auth::user()->department->id]])
            ->orderBy("id", "asc")->get();
        return view('orders.index')->with(['product' => $product])
            ->with(['contact' => $contact])
            ->with(['order_type' => $customCollection]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rnumber = OrderRunningNumber::pre_generate();
        return response()->json([
            'running' =>  $rnumber
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required',
            'cid' => 'required',
            'pid' => 'required',
            'tamount' => 'required',
            'total_cost' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        $order = new Order();
        $order->created_by = Auth::id();
        $order->updated_by = Auth::id();
        $order->order_number  = OrderRunningNumber::generate();
        $order->cid = $request->input('cid');
        $order->pid = $request->input('pid');
        $order->amount = $request->input('tamount');
        $order->total_cost = $request->input('total_cost');
        $order->detail = $request->input('detail');
        $order->save();

        $amounts = $request->input('amount');
        $prices = $request->input('price');
        $sids = $request->input('sid');

        if (is_array($amounts) && is_array($prices) && count($amounts) === count($prices)) {
            foreach ($amounts as $index => $amount) {
                if (isset($sids[$index])) {
                    $orderDetail = new OrderDetail();
                    $orderDetail->order_id = $order->id;
                    $orderDetail->product_id = $sids[$index];
                    $orderDetail->quantity = $amount;
                    $orderDetail->price = $prices[$index];
                    $orderDetail->save();

                    $product = product::find($sids[$index]);
                    if ($product) {
                        $remain = $product->remaining - $amount;
                        if ($remain <= 0) {
                            //set status sold out
                            $product->status_id = 4;
                        }
                        $product->remaining = $remain;
                        $product->save();
                    }
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
                $filestore->module_id = $order->id;
                $filestore->filename = $oldfile[0]->filename;
                $filestore->save();

                File::move(public_path() . '/file_upload/' . $oldfile[0]->filename, public_path() . '/file_store/' . $oldfile[0]->filename);
                FileUpload::where('filename', $oldfile[0]->filename)->delete();
            }
        }

        return response()->json(['success' => 'เพิ่ม รายการขาย เรียบร้อยแล้ว']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Get the data from the database query
        $datas = Order::select(
            "orders.*",
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


        // Render the view with the data and get the HTML content
        $htmlContent = View::make($template, $data)->render();
        //return new Response($htmlContent, 200, ['Content-Type' => 'text/html']);
        return response()->json([
            'html' =>  $htmlContent
        ]);
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
        $datas = OrderDetail::where('order_id', $id)
            ->orderBy("order_details.id", "asc")
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->get();

        $select_list = "";
        $x = 1;
        $data_html = '';
        $etotal = 0;


        //all product
        $datasp = Product::select(
            "products.id as id",
            "products.price as cost",
            "products.name as name",
            "products.unit as unit",
        )
            ->where('type', 1)
            ->orderBy("id", "asc")
            ->get();

        foreach ($datas as $dresult) {
            $select_list = '';
            foreach ($datasp as $keysp) {

                if ($keysp->id == $dresult->product_id) {
                    $select_list .= '<option value="' . $keysp->id . '" selected> ' . $keysp->name . ' </option>';
                } else {
                    $select_list .= '<option value="' . $keysp->id . '" > ' . $keysp->name . ' </option>';
                }
            }

            $etotal = $dresult->quantity * $dresult->price;
            $data_html .= '<tr class="firstTr">
            <td width="30%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <select style="width: 100%;" class="products form-control" id="estock" name="estock[]" required disabled>
                        ' . $select_list . '
                    </select>
                    <div id="elot_price" class="text-success"></div>
                    <div id="elot_error" class="text-danger"></div>
                </div>
            </td>
            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="number" step="0.50" id="eamount" name="eamount[]" class="auto_decimal form-control has-feedback-left" value="' . number_format($dresult->quantity, 2, '.', '') . '" required readonly>
                    <input type="hidden" id="eamounth" name="eamounth[]" value="' . number_format($dresult->quantity, 2, '.', '') . '">
                </div>
            </td>
            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="number" step="0.50" id="eprice" name="eprice[]" class="auto_decimal form-control has-feedback-left" value="' . number_format($dresult->price, 2, '.', '') . '" required readonly>
                </div>
            </td>
            <td width="10%">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="number" step="0.50" id="etotal" name="etotal[]" class="auto_decimal form-control has-feedback-left" value="' . number_format($etotal, 2, '.', '') . '" readonly>
                </div>
            </td>
           <!-- <td width="10%">
                <button type="button" id="removeRowg" class="btn btn-sm btn-danger btnRemoveg"><i class="fa fa-minus"></i></button>
            </td> -->
        </tr>';
        }

        $img = $this->getfileatt($id);

        return response()->json(['data' => $data, 'datas' => $datas, 'table_html' => $data_html, 'imgs' => $img]);
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
    public function update(Request $request, $id)
    {
        //
        $rules = [
            'order_number' => 'required|string|max:20',
            'pid' => 'required|integer|max:255',
            'cid' => 'required|integer|max:255',
        ];


        $validator =  Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $data = [
            'order_number' => $request->get('order_number'),
            'cid' => $request->get('cid'),
            //'amount' => $request->get('tamount'),
            //'cost' => $request->get('cost'),
            'total_price' => $request->get('total_cost'),
            'detail' => $request->get('detail'),
            'refund' => $request->get('refund'),
            'register_fee' => $request->get('register_fee'),
            'promotion' => $request->get('promotion'),
            'updated_by' => Auth::id()
        ];

        $order = Order::find($id);
        $order->update($data);
        OrderDetail::where('order_id', $id)->delete();

        $amounts = $request->input('amount');
        $prices = $request->input('price');
        $sids = $request->input('sid');

        if (is_array($amounts) && is_array($prices) && count($amounts) === count($prices)) {
            foreach ($amounts as $index => $amount) {
                if (isset($sids[$index])) { // Check if the index exists in $sids array
                    $priceq = Product::find($sids[$index]);
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

        return response()->json(['success' => 'Edit Order completed']);
    }

    public function deleteImg($id, $id2)
    {
        $dataimg = FileStore::find($id2)->delete();;

        $html = $this->getfileatt($id);

        return response()->json(['imgs' => $html]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->get('id');
        Order::find($id)->delete();
        $dataimg = FileStore::where([
            'module_id' => $id,
            'module' => 'orders',
        ])->get();

        // Delete the found records
        if (!$dataimg->isEmpty()) {
            $dataimg->each(function ($item) {
                $filename = $item->filename;

                // Build the full path to the file within the 'public/file_store' directory
                $filePath = public_path('file_store/' . $filename);

                // Delete the file if it exists
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $item->delete();
            });
        }
        return ['success' => true, 'message' => 'ลบ รายการขาย เรียบร้อยแล้ว'];
    }

    public function destroy_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            Order::find($arr_del[$xx])->delete();
            $dataimg = FileStore::where([
                'module_id' => $arr_del[$xx],
                'module' => 'orders',
            ])->get();

            // Delete the found records
            if (!$dataimg->isEmpty()) {
                $dataimg->each(function ($item) {
                    $filename = $item->filename;

                    // Build the full path to the file within the 'public/file_store' directory
                    $filePath = public_path('file_store/' . $filename);

                    // Delete the file if it exists
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $item->delete();
                });
            }
        }

        return redirect('/orders')->with('success', 'ลบ รายการขาย เรียบร้อยแล้ว');
    }
}
