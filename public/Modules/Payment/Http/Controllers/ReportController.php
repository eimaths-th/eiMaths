<?php

namespace Modules\Payment\Http\Controllers;

use App\Models\CodPayment;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CourseSetting\Entities\Course;
use Modules\Payment\Entities\Checkout;
use Modules\Payment\Entities\Withdraw;
use Modules\PaymentMethodSetting\Entities\PaymentMethod;


class ReportController extends Controller
{
    public function instructorReveune()
    {

        try {
            $enrolls = Course::withCount('enrolls')->where('user_id', Auth::id())->with('enrolls', 'category', 'subcategory')->orderBy('enrolls_count', 'desc')->paginate(10);
            $user = User::with('currency')->where('id', Auth::user()->id)->first();
            return view('payment::instructor_revenue', compact('enrolls', 'user'));
        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }


    public function withdraws()
    {

        try {
            $logs = Withdraw::with('user')->orderBy('status', 'asc')->latest()->get();
            return view('payment::fund.instructor_payout', compact('logs'));
        } catch (\Exception $e) {

            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }


    public function onlineLog()
    {
        try {
            $gateways = PaymentMethod::where('method', '!=', 'Offline Payment')->latest()->get();
            $onlineLogs = Checkout::where('payment_method', '!=', 'Offline Payment')
                ->with('user')->get();

            return view('payment::fund.online_log', compact('gateways', 'onlineLogs'));
        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }
    public function CODSlip()
    {
        try {
            $gateways = PaymentMethod::where('method', '!=', 'Offline Payment')->latest()->get();
            $codCheckouts = Checkout::where('payment_method', 'COD')
                ->with('user')
                // Apply orderBy before get()
                ->orderBy('id', 'desc')
                ->get()
                ;

            return view('payment::fund.cod_slip', compact('gateways', 'codCheckouts'));
        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }

    public function updateSlipStatus(Request $request, $id)
    {

        // $principal = 1000000;
        // $interestRate = 3;
        // $years = 30;
        // $frequency = 'monthly';
        // $monthlyPayment = 10000;
        // $resultMonthly =  $this->calculateInterest($principal, $interestRate, $years, $monthlyPayment);
        // // Call function for yearly interest calculation
        // // $resultYearly =  $this->calculateInterest($principal, $interestRate, $years, $monthlyPayment);

        // echo "Total principal: " . $principal . "<br>";
        // // echo "Total interest to be paid: " . $resultMonthly['totalInterest'] . "<br>";
        // // echo "Monthly interest payment: " . $resultMonthly['monthlyInterestPayment'] . "<br>";
        // // echo "Monthly principal payment: " . $resultMonthly['monthlyPrincipalPayment'] . "<br>";
        // // echo "Total payments over " . $resultMonthly['years'] . " years: " . $resultMonthly['totalPayments'] . "<br>";
        // // echo "Total interest to be paid after all payments: " . $resultMonthly['totalInterestToBePaid'] . "<br>";
        // dd($request);
        $payment = CodPayment::findOrFail($id);

        $payment->slip_check = $request->slip_check;
        $payment->denial_reason = ($request->slip_check == 3) ? $request->reason : '';
        $payment->save();

        $checkout = $payment->checkout;

        $paid = $checkout->codPayments->where('slip_check', 2)->sum('paid');

        $checkout->update(['paid' => $paid]);

        $total = $checkout->price;

        if ($total - $paid == 0) {
            // If total amount has been paid, set to success
            $checkout->update(['approve_slip' => 2]); // Success
        } else {

            if ($total > $paid) {

                $checkout->update(['approve_slip' => 4]); // Incomplete Payment

            } elseif ($total < $paid) {

                $checkout->update(['approve_slip' => 5]); // over

            } else {

                $checkout->update(['approve_slip' => 1]); // Pending

            }
        }


        return response()->json(['success' => true]);
    }

    function calculateLoanDetails($principal, $interestRate, $years, $monthlyPayment)
    {
        $monthlyInterestRate = ($interestRate / 100) / 12; // Convert annual rate to monthly

        $totalPayments = $monthlyPayment * ($years * 12);
        $totalInterestPaid = 0;
        $remainingPrincipal = $principal;

        // Calculate monthly payments over the loan duration
        for ($i = 0; $i < $years * 12; $i++) {
            $interestPayment = $remainingPrincipal * $monthlyInterestRate;
            $principalPayment = $monthlyPayment - $interestPayment;

            // Ensure that the principal payment does not exceed the remaining principal
            if ($principalPayment > $remainingPrincipal) {
                $principalPayment = $remainingPrincipal;
                $monthlyPayment = $interestPayment + $principalPayment; // Adjust the monthly payment
            }

            // Update the remaining principal
            $remainingPrincipal -= $principalPayment;
            // Accumulate total interest paid
            $totalInterestPaid += $interestPayment;

            // If the principal is paid off, break the loop
            if ($remainingPrincipal <= 0) {
                break;
            }
        }

        return [
            'totalInterestPaid' => $totalInterestPaid,
            'monthlyInterestPayment' => $interestPayment,
            'monthlyPrincipalPayment' => $principalPayment,
            'totalPayments' => $totalPayments,
            'finalPrincipal' => $remainingPrincipal
        ];
    }

    public function filterSearch(Request $request)
    {

        try {
            $gateways = PaymentMethod::where('method', '!=', 'Offline Payment')->get();
            $start = date('Y-m-d', strtotime($request->start_date));
            $end = date('Y-m-d', strtotime($request->end_date));
            $method = $request->methods;

            if ((isset($request->end_date)) && (isset($request->start_date))) {

                if ($method == "all") {

                    $onlineLogs = Checkout::whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->where('payment_method', '!=', 'Offline Payment')->latest()->with('user')->get();
                } else {

                    $onlineLogs = Checkout::whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->where('payment_method', $method)->latest()->with('user')->get();
                }
            } elseif (is_null($request->start_date) && is_null($request->end_date)) {

                if ($method == "all") {

                    $onlineLogs = Checkout::where('payment_method', '!=', 'Offline Payment')->with('user')->get();
                } else {

                    $onlineLogs = Checkout::where('payment_method', $method)->latest()->with('user')->get();
                }
            } elseif (isset($request->start_date) && is_null($request->end_date)) {


                if ($method == "all") {

                    $onlineLogs = Checkout::whereDate('created_at', '>=', $start)->where('payment_method', '!=', 'Offline Payment')->latest()->with('user')->get();
                } else {

                    $onlineLogs = Checkout::whereDate('created_at', '>=', $start)->where('payment_method', $method)->latest()->with('user')->get();
                }
            } elseif (isset($request->end_date) && is_null($start)) {

                if ($method == "all") {

                    $onlineLogs = Checkout::whereDate('created_at', '<=', $end)->where('payment_method', '!=', 'Offline Payment')->latest()->with('user')->get();
                } else {

                    $onlineLogs = Checkout::whereDate('created_at', '<=', $end)->where('payment_method', $method)->latest()->with('user')->get();
                }
            }
            return view('payment::fund.online_log', compact('gateways', 'onlineLogs'));
        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }
}
