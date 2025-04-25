<?php

namespace App\Http\Controllers;

use App\Models\AffiliateUserBank;
use App\Models\PVNumber;
use Illuminate\Http\Request;
use Modules\Affiliate\Entities\AffiliateWithdraw;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Spatie\Permission\Models\Role;


class ConfigWithdrawController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //

        if (!Gate::allows('all-centre')) {
            $roles = Role::pluck('name', 'id')->except([1, 4])->all();
        } else {
            $roles = Role::pluck('name', 'name')->all();
        }
        if ($request->ajax()) {
            //sleep(2);

            $datas =  AffiliateWithdraw::query()->orderBy("id", "desc")->get();

            return datatables()->of($datas)
                ->editColumn('user_id', function ($row) {
                    return $row->user->name ?? 'N/A';
                })
                ->editColumn('withdraw_amount', function ($row) {
                    return number_format($row->withdraw_amount ?? '0', 2) . ' ฿';
                })
                ->editColumn('account', function ($row) {
                    $account = '';
                    if ($row->payment_type == 1) {
                        # code...PromptPay
                        $type = AffiliateUserWallet::where('user_id', $row->user_id)->first();
                        $account = $type->paypal_account ?? 'Paypal Account is not found';
                    } elseif ($row->payment_type == 2) {
                        # code...
                        $type = AffiliateUserBank::where('user_id', $row->user_id)->first();

                        $account_number = $type->account_number ?? 'Account Number is not found';
                        $bank_name = $type->bank_name ?? 'Bank Name is not found';

                        $account = '' . $account_number . ' | ' . $bank_name;
                    }
                    return $account;
                })
                ->editColumn('payment_type', function ($row) {
                    if ($row->payment_type == 1) {
                        # code...PromptPay
                        $type = 'PromptPay';
                    } elseif ($row->payment_type == 2) {
                        # code...
                        $type = 'Bank Account';
                    }
                    return $type ?? 'N/A';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 0) {
                        # code...
                        $status = 'Pending';
                        $class = 'warning';
                    } elseif ($row->status == 1) {
                        # code...
                        $status = 'Done';
                        $class = 'success';
                    } elseif ($row->status == 2) {
                        # code...
                        $status = 'Cancel';
                        $class = 'danger';
                    } else {
                        # code...
                        $status = 'error ';
                        $class = 'dark';
                    }
                    $html = '<span class="badge bg-' . $class . ' rounded-pill">' . $status . ' </span>';
                    return $html;
                })
                ->editColumn('request_date', function ($row) {
                    return $row->request_date ?? '';
                })
                ->editColumn('confirmed_by', function ($row) {
                    return $row->confirmedBy->name ?? '';
                })
                ->editColumn('confirm_date', function ($row) {
                    return $row->confirm_date ?? '';
                })
                ->editColumn('rejected_by', function ($row) {
                    return $row->rejectedBy->name ?? '';
                })
                ->editColumn('reject_date', function ($row) {
                    return $row->reject_date ?? '';
                })
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" id="' . $row->id . '" class="flat" name="table_records[]" value="' . $row->id . '" >';
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    if ($row->status == 0) {
                        // Button for "Approve"
                        $html .= '<button class="btn btn-success btn-sm approve-withdraw-btn" data-id="' . $row->id . '">Approve</button>';
                        // Button for "Reject"
                        $html .= ' <button class="btn btn-danger btn-sm reject-withdraw-btn" data-id="' . $row->id . '">Reject</button>';
                    } else {
                        # code...
                        $html .= '<button class="btn btn-primary btn-sm view-btn" data-id="' . $row->id . '">Report</button>';
                    }
                    return $html;
                })->rawColumns(['checkbox', 'action', 'status'])->toJson();
            // dd($datas);

        }

        // $withdraw =  AffiliateWithdraw::all();
        return view('config_withdraw.index');
        // return view('config-withdraw.index', [
        //     'withdraw' => $withdraw,
        // ]);
    }

    public function approve_withdraw(Request $request)
    {
        // Validate incoming request
        $id = $request->id;
        $action = $request->action;

        if (!in_array($action, ['approve', 'reject'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        // Find the withdraw request
        $withdrawRequest = AffiliateWithdraw::find($id);
        if (!$withdrawRequest) {
            return response()->json(['error' => 'Withdraw request not found'], 404);
        }

        // Find the user's wallet
        $UserWallet = AffiliateUserWallet::where('user_id', $withdrawRequest->user_id)->first();
        if (!$UserWallet) {
            return response()->json(['error' => 'User wallet not found'], 404);
        }

        //find the user's bank
        $UserBank = AffiliateUserBank::where('user_id', $withdrawRequest->user_id)->first();
        if (!$UserBank) {
            return response()->json(['error' => 'User bank not found'], 404);
        }


        // Handle the "approve" action
        if ($action === 'approve') {


            $walletBalance = $UserWallet->amount;
            $withdrawAmount = (float) $withdrawRequest->withdraw_amount; // Assuming the field is 'amount'

            // Check if the user has sufficient balance
            // if ($walletBalance < $withdrawAmount) {
            //     return response()->json(['error' => 'Insufficient wallet balance'], 400);
            // }
            // dd($withdrawAmount, $walletBalance, $UserWallet, $withdrawRequest);


            // Deduct the withdrawal amount from the wallet
            // $newBalance = $walletBalance - $withdrawAmount;
            // $UserWallet->update(['amount' => $newBalance]);
            // $UserWallet->where('user_id', $withdrawRequest->user_id)->increment('amount', $withdrawRequest->amount);
            $UserWallet->decrement('amount', $withdrawAmount);
            // Update withdraw request status to approved
            $withdrawRequest->status = 1; // Assuming 1 means "Approved"
            $withdrawRequest->confirmed_by = auth()->id(); // Track who approved
            $withdrawRequest->confirm_date = now(); // Track confirmation time
            $withdrawRequest->save();

            return response()->json(['success' => true, 'message' => 'Withdraw request approved successfully']);
        }

        // Handle the "reject" action
        if ($action === 'reject') {
            // Update withdraw request status to rejected
            $withdrawRequest->status = 2; // Assuming 2 means "Rejected"
            $withdrawRequest->rejected_by = auth()->id(); // Track who rejected
            $withdrawRequest->reject_date = now(); // Track rejection time
            $withdrawRequest->save();
            return response()->json(['success' => true, 'message' => 'Withdraw request rejected successfully']);
        }

        // Fallback if no valid action is processed
        return response()->json(['error' => 'Unexpected error occurred'], 500);
    }

    public function approveWithdraws(Request $request)
    {
        $withdrawIds = $request->input('withdraw_ids');
        $action = $request->input('action');

        // Check if IDs and action are provided
        if (empty($withdrawIds)) {
            return response()->json(['error' => 'No records selected'], 400);
        }

        if (!in_array($action, ['approve', 'reject'])) {
            return response()->json(['error' => 'Invalid action'], 400);
        }

        // Get all withdraw requests
        $withdrawRequests = AffiliateWithdraw::whereIn('id', $withdrawIds)->get();

        if ($withdrawRequests->isEmpty()) {
            return response()->json(['error' => 'Withdraw requests not found'], 404);
        }

        foreach ($withdrawRequests as $withdrawRequest) {
            $UserWallet = AffiliateUserWallet::where('user_id', $withdrawRequest->user_id)->first();

            if (!$UserWallet) {
                return response()->json(['error' => "User wallet not found for ID {$withdrawRequest->user_id}"], 404);
            }

            if ($action === 'approve') {
                $withdrawAmount = (float) $withdrawRequest->withdraw_amount;
                // Deduct the withdrawal amount
                // $UserWallet->decrement('amount', $withdrawAmount);

                // Update the withdraw status to approved
                $withdrawRequest->status = 1;
                $withdrawRequest->confirmed_by = auth()->id();
                $withdrawRequest->confirm_date = now();
                $withdrawRequest->save();
            } elseif ($action === 'reject') {
                $withdrawRequest->status = 2;
                $withdrawRequest->rejected_by = auth()->id() ?? null; // Ensure this field exists in your schema
                $withdrawRequest->reject_date = now();
                $withdrawRequest->save();
            }
        }

        $message = $action === 'approve' ? 'Withdraw requests approved successfully' : 'Withdraw requests rejected successfully';

        return response()->json(['success' => true, 'message' => $message]);
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

    public function runningNumber()
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->id;
        $withdraw = AffiliateWithdraw::find($id);
        // dd(
        //     $request->all(),
        //     $withdraw
        // );
        if (!$withdraw) {
            return response()->json(['message' => 'Commission record not found'], 404);
        }

        
        // dd($data);

        $template = 'config_withdraw.view_report_modal';
        $account = '';
        if ($withdraw->payment_type == 1) {
            # code...PromptPay
            $type = AffiliateUserWallet::where('user_id', $withdraw->user_id)->first();
            $account = $type->paypal_account ?? 'Paypal Account is not found';
        } elseif ($withdraw->payment_type == 2) {
            # code...
            $type = AffiliateUserBank::where('user_id', $withdraw->user_id)->first();

            $account_number = $type->account_number ?? 'Account Number is not found';
            $bank_name = $type->bank_name ?? 'Bank Name is not found';

            $account = '' . $account_number . ' ธ.' . $bank_name;
        }
        
        if (!$withdraw->pv_number) {
            # code...
            $pvNumber = PVNumber::runningNumber();
            $withdraw->pv_number = $pvNumber;
            $withdraw->save();
        }

        $htmlContent = View::make(
            $template,
            [
                'withdraw' => $withdraw,
                'account' => $account,
            ]
        )->render();

        return response()->json([
            'html' =>  $htmlContent

        ]);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
