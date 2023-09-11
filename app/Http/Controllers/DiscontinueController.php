<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Auth;

class DiscontinueController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('permission:contact-list|contact-create|contact-edit|contact-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:contact-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:contact-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:contact-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Contact::select(
                "contacts.*",
                "departments.name as centre_name",
            )->join("departments", "contacts.centre", "=", "departments.id")
                ->where('discontinued', '1');

            if (!empty($request->get('sdate'))) {
                //dd($request->get('sdate'));
                //$query->where('contacts.code', $request->get('approved'));
                $dateRange = $request->input('sdate');

                // Check if the dateRange parameter is provided
                if ($dateRange) {
                    $dateRangeArray = explode(' - ', $dateRange);

                    if (!empty($dateRangeArray) && count($dateRangeArray) == 2) {
                        $startDate = $dateRangeArray[0];
                        $endDate = $dateRangeArray[1];
                        $query->whereBetween('contacts.discontinued_date', [$startDate, $endDate]);
                    }
                }
            }

            $query->orderBy("contacts.id", "desc");
            $datas = $query->get();

            return datatables()->of($datas)
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('more', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('contact-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-rowid="' . $row->id . '"><i class="fa-solid fa-retweet"></i> Restart</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa-solid fa-retweet"></i> Restart</button> ';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action', 'more'])->toJson();
        }

        return view('discontinued.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restart(Request $request)
    {
        $id = $request->get('id');
        $newData = [
            'discontinued' => '0',
            'discontinued_date' => '',
            'discontinued_reason' => ''

        ];

        Contact::find($id)->update($newData);

        return ['success' => true, 'message' => 'Restart student successfully'];
    }

    public function restart_all(Request $request)
    {

        $arr_del  = $request->get('table_records'); //$arr_ans is Array MacAddress

        for ($xx = 0; $xx < count($arr_del); $xx++) {
            $newData = [
                'discontinued' => '0',
                'discontinued_date' => '',
                'discontinued_reason' => ''

            ];

            Contact::find($arr_del[$xx])->update($newData);
        }

        return redirect('/contacts')->with('success', 'Restart student successfully');
    }
}
