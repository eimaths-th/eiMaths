<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Receipt;
use App\Models\Histrories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
/* use Carbon\Carbon;
use DateInterval;
use DateTime;
use Exception; */

class DashboardController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        //$this->middleware('permission:dashboard-view', ['only' => ['index']]);
    }

    function formatDuration($sec)
    {

        $t = round($sec);
        return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('dashboard.index');
    }



    public function dashboard_student_by_level(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        // Ignore date_range and years filters

        $results = Contact::select(
            'level_name',
            DB::raw('COUNT(*) as count')
        );

        // Apply centre filter
        if ($dcentreValue && $dcentreValue !== '1') {
            $results->where('centre', $dcentreValue);
        }

        // Exclude Test (4) and Affiliate (5) centres
        $results->whereNotIn('centre', [4, 5]);

        $results->groupBy('level_name');
        $datas = $results->get();

        // Modify the code to find max data directly from the database query
        $maxData = $datas->max('count');

        $dataCounts = $datas->pluck('count', 'level_name')->all();

        ksort($dataCounts);

        return response()->json([
            'level_data' => $dataCounts,
            'max_data' => $maxData,
        ]);
    }

    public function dashboard_student_by_centre(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        $dateRangeValue = $request->input('date_range');
        $yearsValue = $request->input('years');

        $results = Contact::select(
            'contacts.centre as centre_id',  // Alias for clarity
            'departments.name as centre_name', // Alias for clarity
            DB::raw('COUNT(*) as count')
        )
            ->join('departments', 'contacts.centre', '=', 'departments.id') // Adjust the join condition based on your actual schema
            ->where('contacts.centre', '!=', 1);

        if (!Gate::allows('all-centre')) {
            $results->where('contacts.centre', $dcentreValue);
        }

        // Apply date range filter if provided
        if ($dateRangeValue) {
            $dates = explode(' - ', $dateRangeValue);
            if (count($dates) === 2) {
                $results->whereBetween('contacts.created_at', [$dates[0], $dates[1] . ' 23:59:59']);
            }
        }

        // Apply years filter if provided
        if ($yearsValue && is_array($yearsValue) && count($yearsValue) > 0) {
            $results->where(function ($query) use ($yearsValue) {
                foreach ($yearsValue as $year) {
                    $query->orWhereYear('contacts.created_at', $year);
                }
            });
        }

        $results->groupBy('contacts.centre', 'departments.name'); // Group by both centre columns
        $datas = $results->get();

        $maxData = $datas->max('count');

        $dataCounts = $datas->pluck('count', 'centre_name')->all();
        $dataCountsi = $datas->pluck('count', 'centre_id')->all();

        //dd($dcentreValue);
        //dd($dataCountsi);

        $totalStudent = 0; // Initialize totalStudent

        if ($dcentreValue && $dcentreValue !== '1') {
            $totalStudent = $dataCountsi[$dcentreValue] ?? 0;
        } else {
            $totalStudent = $datas->sum('count');
        }

        $totalCentres = count($dataCounts);

        ksort($dataCounts);
        return response()->json([
            'total_student' => $totalStudent,
            'total_centre' => $totalCentres,
            'centre_data' => $dataCounts,
            'max_data' => $maxData,
        ]);
    }

    public function dashboard_teacher_by_centre(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        $dateRangeValue = $request->input('date_range');
        $yearsValue = $request->input('years');
        $role = 'Teacher';

        $results = User::select(
            'users.department_id as centre_id',
            'departments.name as centre_name',
            DB::raw('COUNT(*) as count')
        )
            ->join('departments', 'users.department_id', '=', 'departments.id') // Adjust the join condition based on your actual schema
            //->where('users.department_id', '!=', 1);
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            });

        if (!Gate::allows('all-centre')) {
            $results->where('users.department_id', $dcentreValue);
        }

        // Apply date range filter if provided
        if ($dateRangeValue) {
            $dates = explode(' - ', $dateRangeValue);
            if (count($dates) === 2) {
                $results->whereBetween('users.created_at', [$dates[0], $dates[1] . ' 23:59:59']);
            }
        }

        // Apply years filter if provided
        if ($yearsValue && is_array($yearsValue) && count($yearsValue) > 0) {
            $results->where(function ($query) use ($yearsValue) {
                foreach ($yearsValue as $year) {
                    $query->orWhereYear('users.created_at', $year);
                }
            });
        }

        $results->groupBy('users.department_id', 'departments.name'); // Group by both centre columns
        $datas = $results->get();

        $maxData = $datas->max('count');

        $dataCounts = $datas->pluck('count', 'centre_name')->all();
        $dataCountsi = $datas->pluck('count', 'centre_id')->all();

        //dd($dcentreValue);
        //dd($dataCountsi);

        $totalTeacher = 0; // Initialize totalStudent

        if ($dcentreValue && $dcentreValue !== '1') {
            $totalTeacher = $dataCountsi[$dcentreValue] ?? 0;
        } else {
            $totalTeacher = $datas->sum('count');
        }



        ksort($dataCounts);
        return response()->json([
            'total_teacher' => $totalTeacher,
            'centre_data' => $dataCounts,
            'max_data' => $maxData,
        ]);
    }


    public function today_study_student_by_centre(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        $dateRangeValue = $request->input('date_range');
        $yearsValue = $request->input('years');

        $results = Histrories::select(
            'histrories.centre as centre_id',
            'departments.name as centre_name',
            DB::raw('COUNT(*) as count')
        )
            ->join('departments', 'histrories.centre', '=', 'departments.id'); // Adjust the join condition based on your actual schema

        if (!Gate::allows('all-centre')) {
            $results->where('histrories.centre', $dcentreValue);
        }

        // Apply date range filter if provided, otherwise use today
        if ($dateRangeValue) {
            $dates = explode(' - ', $dateRangeValue);
            if (count($dates) === 2) {
                $results->whereBetween('histrories.date', [$dates[0], $dates[1]]);
            }
        } else {
            $results->where('histrories.date', date("Y-m-d"));
        }

        // Apply years filter if provided
        if ($yearsValue && is_array($yearsValue) && count($yearsValue) > 0) {
            $results->where(function ($query) use ($yearsValue) {
                foreach ($yearsValue as $year) {
                    $query->orWhereYear('histrories.date', $year);
                }
            });
        }

        $results->groupBy('histrories.centre', 'departments.name'); // Group by both centre columns
        $datas = $results->get();

        $maxData = $datas->max('count');

        $dataCounts = $datas->pluck('count', 'centre_name')->all();
        $dataCountsi = $datas->pluck('count', 'centre_id')->all();

        //dd($dcentreValue);
        //dd($dataCountsi);

        $totalStudy = 0; // Initialize totalStudent

        if ($dcentreValue && $dcentreValue !== '1') {
            $totalStudy = $dataCountsi[$dcentreValue] ?? 0;
        } else {
            $totalStudy = $datas->sum('count');
        }



        ksort($dataCounts);
        return response()->json([
            'total_study' => $totalStudy,
            'centre_data' => $dataCounts,
            'max_data' => $maxData,
        ]);
    }


    public function receipt_sum_by_date(Request $request)
    {
        // dd($request->all());
        $dcentreValue = $request->input('dcentre');
        $dateRangeValue = $request->input('date_range');
        $yearsValue = $request->input('years');

        $summaryByDate = Receipt::select(
            'payment_date',
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('COUNT(CASE WHEN orders.status = 1 THEN 1 ELSE NULL END) as total_transactions_payment'),
            DB::raw('COUNT(CASE WHEN orders.status = 0 THEN 1 ELSE NULL END) as total_transactions_pending'),
            DB::raw('SUM(CASE WHEN orders.status = 1 THEN total_fee ELSE 0 END) as total_fee_sum')
        )
            ->join('orders', 'receipts.oid', '=', 'orders.id');

        if ($dcentreValue && $dcentreValue !== '1') {
            $summaryByDate->where('receipts.centre', $dcentreValue);
        }
        $summaryByDate->whereNot('receipts.centre', 4);
        //$summaryByDate->where('orders.status', 1);

        // Apply date range filter if provided
        if ($dateRangeValue) {
            $dates = explode(' - ', $dateRangeValue);
            if (count($dates) === 2) {
                $summaryByDate->whereBetween('payment_date', [$dates[0], $dates[1]]);
            }
        }

        // Apply years filter if provided
        if ($yearsValue && is_array($yearsValue) && count($yearsValue) > 0) {
            $summaryByDate->where(function ($query) use ($yearsValue) {
                foreach ($yearsValue as $year) {
                    $query->orWhereYear('payment_date', $year);
                }
            });
        }

        $summaryByDate->groupBy('payment_date');
        $datas = $summaryByDate->get();

        $totalTransactionsSum = $datas->sum('total_transactions');
        $totalTransactionsPay = $datas->sum('total_transactions_payment');
        $totalTransactionsPen = $datas->sum('total_transactions_pending');
        $totalFeeSum = $datas->sum('total_fee_sum');

        return response()->json([
            'total_receipt' => $totalTransactionsSum,
            'total_receipt_pay' => $totalTransactionsPay,
            'total_receipt_pen' => $totalTransactionsPen,
            'total_income' => $totalFeeSum,
            'receipt_sum_by_date' => $datas,
        ]);
    }

    public function receipt_sum_by_month(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        $dateRangeValue = $request->input('date_range');
        $yearsValue = $request->input('years');

        $summaryByMonth = Receipt::select(
            DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('COUNT(CASE WHEN orders.status = 1 THEN 1 ELSE NULL END) as total_transactions_payment'),
            DB::raw('COUNT(CASE WHEN orders.status = 0 THEN 1 ELSE NULL END) as total_transactions_pending'),
            DB::raw('SUM(CASE WHEN orders.status = 1 THEN total_fee ELSE 0 END) as total_fee_sum')
        )
            ->join('orders', 'receipts.oid', '=', 'orders.id');

        if ($dcentreValue && $dcentreValue !== '1') {
            $summaryByMonth->where('receipts.centre', $dcentreValue);
        }
        $summaryByMonth->whereNot('receipts.centre', 4);

        // Apply date range filter if provided
        if ($dateRangeValue) {
            $dates = explode(' - ', $dateRangeValue);
            if (count($dates) === 2) {
                $summaryByMonth->whereBetween('payment_date', [$dates[0], $dates[1]]);
            }
        }

        // Apply years filter if provided
        if ($yearsValue && is_array($yearsValue) && count($yearsValue) > 0) {
            $summaryByMonth->where(function ($query) use ($yearsValue) {
                foreach ($yearsValue as $year) {
                    $query->orWhereYear('payment_date', $year);
                }
            });
        }

        $summaryByMonth->groupBy('month');
        $datas = $summaryByMonth->get();

        $totalTransactionsSum = $datas->sum('total_transactions');
        $totalTransactionsPay = $datas->sum('total_transactions_payment');
        $totalTransactionsPen = $datas->sum('total_transactions_pending');
        $totalFeeSum = $datas->sum('total_fee_sum');

        return response()->json([
            'total_receipt' => $totalTransactionsSum,
            'total_receipt_pay' => $totalTransactionsPay,
            'total_receipt_pen' => $totalTransactionsPen,
            'total_income' => $totalFeeSum,
            'receipt_sum_by_month' => $datas,
        ]);
    }

    public function receipt_sum_by_year(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        $dateRangeValue = $request->input('date_range');
        $yearsValue = $request->input('years');

        $summaryByYear = Receipt::select(
            DB::raw('YEAR(payment_date) as year'),
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('COUNT(CASE WHEN orders.status = 1 THEN 1 ELSE NULL END) as total_transactions_payment'),
            DB::raw('COUNT(CASE WHEN orders.status = 0 THEN 1 ELSE NULL END) as total_transactions_pending'),
            DB::raw('SUM(CASE WHEN orders.status = 1 THEN total_fee ELSE 0 END) as total_fee_sum')
        )
            ->join('orders', 'receipts.oid', '=', 'orders.id');

        if ($dcentreValue && $dcentreValue !== '1') {
            $summaryByYear->where('receipts.centre', $dcentreValue);
        }
        $summaryByYear->whereNot('receipts.centre', 4);

        // Apply date range filter if provided
        if ($dateRangeValue) {
            $dates = explode(' - ', $dateRangeValue);
            if (count($dates) === 2) {
                $summaryByYear->whereBetween('payment_date', [$dates[0], $dates[1]]);
            }
        }

        // Apply years filter if provided
        if ($yearsValue && is_array($yearsValue) && count($yearsValue) > 0) {
            $summaryByYear->where(function ($query) use ($yearsValue) {
                foreach ($yearsValue as $year) {
                    $query->orWhereYear('payment_date', $year);
                }
            });
        }

        $summaryByYear->groupBy('year');
        $datas = $summaryByYear->get();

        $totalTransactionsSum = $datas->sum('total_transactions');
        $totalTransactionsPay = $datas->sum('total_transactions_payment');
        $totalTransactionsPen = $datas->sum('total_transactions_pending');
        $totalFeeSum = $datas->sum('total_fee_sum');

        return response()->json([
            'total_receipt' => $totalTransactionsSum,
            'total_receipt_pay' => $totalTransactionsPay,
            'total_receipt_pen' => $totalTransactionsPen,
            'total_income' => $totalFeeSum,
            'receipt_sum_by_year' => $datas,
        ]);
    }

    public function dashboard_student_status(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        // Ignore date_range and years filters

        $results = Contact::select(
            //'contacts.*',
            'contacts.centre as centre_id',
            'departments.name as centre_name',
            DB::raw('COUNT(CASE WHEN discontinued = 0 THEN 1 ELSE NULL END) as count'),
            DB::raw('COUNT(CASE WHEN discontinued = 1 THEN 1 ELSE NULL END) as count_dis'),
            DB::raw('COUNT(CASE WHEN discontinued = 2 THEN 1 ELSE NULL END) as count_gradute'),
        )

            ->join('departments', 'contacts.centre', '=', 'departments.id');

        // Apply centre filter
        if ($dcentreValue && $dcentreValue !== '1') {
            $results->where('contacts.centre', $dcentreValue);
        }

        // Exclude Test (4) and Affiliate (5) centres
        $results->whereNotIn('contacts.centre', [4, 5]);

        $results->groupBy(['centre_name', 'contacts.centre']);
        $datas = $results->get();

        //dd($datas);

        // Modify the code to find max data directly from the database query
        $totalTransactionsSum = $datas->sum('count');
        $totaldis = $datas->sum('count_dis');
        $totalgradute = $datas->sum('count_gradute');
        // dd($totalTransactionsSum, $totaldis, $totalgradute, $datas);
        return response()->json([
            'total_student' => $totalTransactionsSum,
            'total_discontinue' => $totaldis,
            'total_gradute' => $totalgradute,
            'student_status' => $datas,
        ]);
    }


    public function daily_study(Request $request)
    {
        $dcentreValue = $request->input('dcentre');
        $dateRangeValue = $request->input('date_range');
        $yearsValue = $request->input('years');

        $query = Histrories::select(
            'date',
            DB::raw('COUNT(*) as total_transactions')
        );

        if ($dcentreValue && $dcentreValue !== '1') {
            $query->where('centre', $dcentreValue);
        }

        // Apply date range filter if provided
        if ($dateRangeValue) {
            $dates = explode(' - ', $dateRangeValue);
            if (count($dates) === 2) {
                $query->whereBetween('date', [$dates[0], $dates[1]]);
            }
        }

        // Apply years filter if provided
        if ($yearsValue && is_array($yearsValue) && count($yearsValue) > 0) {
            $query->where(function ($q) use ($yearsValue) {
                foreach ($yearsValue as $year) {
                    $q->orWhereYear('date', $year);
                }
            });
        }

        $query->groupBy('date');
        $datas = $query->get();

        return response()->json([
            'daily_study' => $datas,
        ]);
    }
}
