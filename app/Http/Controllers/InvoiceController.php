<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Order;
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

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            )

                ->join("contacts", "contacts.id", "=", "invoices.cid")
                ->join("departments", "contacts.centre", "=", "departments.id")
                ->join("orders", "orders.id", "=", "invoices.id")
                ->leftJoin("order_details as first_detail", function ($join) {
                    $join->on("first_detail.order_id", "=", "orders.id")
                        ->whereRaw("first_detail.id = (SELECT MIN(id) FROM order_details WHERE order_id = orders.id)");
                })
                ->join("bookuses", "bookuses.id", "=", "first_detail.product_id")
                ->where('invoices.status', 0);


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
                ->editColumn('book_level', function ($row) use (&$nextBookPrice) {
                    if ($row->book_term == 4) {
                        $nextbook = bookuse::select("bookuses.*", "levels.price as nprice")
                            ->join("levels", "levels.id", "=", "bookuses.level_id")
                            ->where('level_id', '>', $row->book_level)->orderBy('level_id')->first();
                    } else {
                        $nextbook = bookuse::select("bookuses.*", "levels.price as nprice")
                            ->join("levels", "levels.id", "=", "bookuses.level_id")
                            ->where('level_id', '=', $row->book_level)
                            ->where('term_id', '>', $row->book_term)->orderBy('id')->first();
                    }

                    //$nextBookPrice = "";

                    if ($nextbook) {
                        $nextBookPrice = $nextbook->nprice;
                        return $nextbook->name;
                    } else {
                        // Handle the case when there is no next book
                        return 'No next book';
                    }
                })
                ->addColumn('next_price', function () use (&$nextBookPrice) {
                    // Now you can access $nextBookPrice here
                    return $nextBookPrice;
                })
                ->editColumn('created_at', function (Invoice $datas) {
                    return date('Y-m-d', strtotime($datas->created_at));
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
                })->rawColumns(['checkbox', 'action', 'next_price'])->toJson();
        }

        $customData = [
            [
                'id' => 1,
                'name' => 'Invoice',
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

        return view('invoices.pending')->with(['product' => $product])
            ->with(['contact' => $contact])
            ->with(['order_type' => $customCollection])
            ->with(['centre' => $centre])
            ->with(['level' => $level])
            ->with(['term' => $term])
            ->with(['sterm' => $sterm]);
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
    public function show(Invoice $invoice)
    {
        //
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
}
