<?php

namespace App\Http\Controllers;

use App\Models\affiliateConfigCommission;
use App\Models\Contact;
use App\Models\term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Affiliate\Entities\AffiliateConfiguration;


class AffiliateConfigurationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $aff_config = AffiliateConfiguration::all();
        $aff_comission = affiliateConfigCommission::all();
        $results = activeStudent();
        $results2 = getRecieptWithRef();


        // dd($results2);



        // $aff_config_commission = affiliateConfigCommission::all();

        // $getIdCommission = 0;

        // foreach ($aff_config_commission as $commissoin) {
        //     # code...
        //     if ($results >= $commissoin->course_per_month) {
        //         $getIdCommission = $commissoin->id;
        //     }
        // }


        // $config_commission = affiliateConfigCommission::find($getIdCommission);

        // $user_id = 21;
        // $month = date('m');
        // $year = date('Y');
        // checkCourseCurrent($user_id, $year, $month, $config_commission);

        return view('affiliate.config.index', [
            'aff_config' => $aff_config,
            'aff_comission' => $aff_comission,
            'count_std' => $results,
            'results2' => $results2,
        ]);
    }

    // public function dashboard_student_status(Request $request)
    // {

    //     $dcentreValue = $request->input('dcentre');
    //     $reservationValue = $request->input('reservation');

    //     $results = Contact::select(
    //         //'contacts.*',
    //         DB::raw('COUNT(CASE WHEN discontinued = 0 THEN 1 ELSE NULL END) as count'),
    //         DB::raw('COUNT(CASE WHEN discontinued = 1 THEN 1 ELSE NULL END) as count_dis'),
    //         DB::raw('COUNT(CASE WHEN discontinued = 2 THEN 1 ELSE NULL END) as count_gradute'),
    //     );
    //     $datas = $results->get();

    //     //dd($datas);

    //     // Modify the code to find max data directly from the database query
    //     $totalTransactionsSum = $datas->sum('count');
    //     $totaldis = $datas->sum('count_dis');
    //     $totalgradute = $datas->sum('count_gradute');

    //     return response()->json([
    //         'total_student' => $totalTransactionsSum,
    //         'total_discontinue' => $totaldis,
    //         'total_gradute' => $totalgradute,
    //         'student_status' => $datas,
    //     ]);
    // }
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request)
    {
        // dd($request);
        $validatedData = $request->validate([
            'min_withdraw' => 'required|numeric|min:0',
            'balance_add_account_after_days' => 'required|integer|min:0',
            'commission_amount' => 'required|numeric|min:0',
        ]);

        // Update values in the database
        foreach ($validatedData as $key => $value) {
            AffiliateConfiguration::where('key', $key)
                ->update(['value' => $value]);
        }

        return response()->json(['success' => true]);
    }

    public function updateConfigComission(Request $request)
    {

        $request->validate([
            'course_per_month.*' => 'required|numeric|min:0',
            'user_per_course_low.*' => 'required|numeric|min:0',
            'user_per_course_high.*' => 'required|numeric|min:0',
            'comission_per_course_10_percent.*' => 'required|numeric|min:0',
            'comission_per_course_15_percent.*' => 'required|numeric|min:0',
        ]);

        foreach ($request->id as $id) {
            $commission = affiliateConfigCommission::find($id);

            if ($commission) {
                $commission->course_per_month = $request->course_per_month[$id];
                $commission->user_per_course_low = $request->user_per_course_low[$id];
                $commission->user_per_course_high = $request->user_per_course_high[$id];
                $commission->comission_per_course_10_percent = $request->comission_per_course_10_percent[$id];
                $commission->comission_per_course_15_percent = $request->comission_per_course_15_percent[$id];

                $commission->save();
            }
        }

        return redirect()->back()->with('success', 'Commission data updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
