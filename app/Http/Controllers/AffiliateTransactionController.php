<?php

namespace App\Http\Controllers;

use App\Models\AffiliateUserBank;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Modules\Affiliate\Http\Requests\AffiliateWithdrawRequest;
use Modules\Affiliate\Http\Requests\BalanceTransferRequest;
use Modules\Affiliate\Repositories\AffiliateTransactionRepository;
use Yajra\DataTables\Facades\DataTables;

class AffiliateTransactionController extends Controller
{
    protected $affiliateTransactionRepo;

    public function __construct(AffiliateTransactionRepository $affiliateTransactionRepo)
    {
        $this->affiliateTransactionRepo = $affiliateTransactionRepo;
    }

    // public function store(Request $request)
    // {
    //     echo 'test';
    //     dd($request);
    // }
    public function store(AffiliateWithdrawRequest $request)
    {

        // dd($request->all());
        $id = $request->user_id;

        $UserWallet = AffiliateUserWallet::where('user_id', $id)->first();
        if (!$UserWallet) {
            return response()->json(['error' => 'Prompt Pay account not found'], 404);
        }

        if ($request->payment_type == 2) {
            # code...
            $UserBank = AffiliateUserBank::where('user_id', $id)->first();

            if (!$UserBank) {
                return response()->json(['error' => 'Bank account not found'], 404);
            }
        }


        // Find the user's wallet


        try {
            DB::beginTransaction();
            $this->affiliateTransactionRepo->withdrawRequest($request->validated());
            DB::commit();
            return response()->json(['status' => 200]);
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), trans('common.error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }


    public function balanceTransferToWallet(BalanceTransferRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->affiliateTransactionRepo->balanceTransferToWallet($request->validated());
            DB::commit();
            return response()->json(['status' => 200]);
        } catch (Exception $e) {

            DB::rollBack();
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }
    public function edit($id)
    {
        try {
            $data['user'] = Auth::user();
            $affiliate_wallet = $data['user']->affiliateWallet;
            if ($affiliate_wallet && $affiliate_wallet->paypal_account) {
                $data['paypal_account'] = $affiliate_wallet->paypal_account;
            }
            $data['transaction'] = $this->affiliateTransactionRepo->find($id);
            if ($data['user']->role_id == 3) {
                return view('affiliate::student.components._edit_withdraw_request_modal', $data);
            }
            return view('affiliate::affiliate.components._edit_withdraw_request_modal', $data);
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function update(Request $request, $id)
    {

        if (isset($request->balance_transfer_request)) {
            $validate_rules = [
                'user_id' => 'required',
                'payment_type' => 'required',
                'transfer_amount' => 'required',
            ];
        } else {
            $validate_rules = [
                'user_id' => 'required',
                'withdraw_amount' => 'required',
                'payment_type' => 'required',
            ];
        }
        $request->validate($validate_rules, validationMessage($validate_rules));

        try {

            DB::beginTransaction();
            $this->affiliateTransactionRepo->update($request->all(), $id);
            DB::commit();
            return response()->json(['status' => 200]);
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            $this->affiliateTransactionRepo->delete($request->id);
            DB::commit();
            return response()->json(['status' => 200]);
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function pendingWithdraw()
    {
        try {
            $data['data'] = $this->affiliateTransactionRepo->pendingWithdraw();
            return view('affiliate::withdraw.pending_withdraw', $data);
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function pendingWithdrawDatatable(Request $request)
    {
        try {
            $data['start_date'] = '';
            $data['end_date'] = '';
            if ($request->filter_date) {
                $date = explode('-', $request->filter_date);
                $data['start_date'] = date('Y-m-d', strtotime($date[0]));
                $data['end_date'] = date('Y-m-d', strtotime($date[1]));
            }
            $data = $this->affiliateTransactionRepo->pendingWithdrawQuery($data['start_date'], $data['end_date']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return showDate($row->request_date);
                })
                ->addColumn('amount', function ($row) {
                    return getPriceFormat($row->withdraw_amount);
                })
                ->addColumn('payment_type', function ($row) {
                    if ($row->payment_type == 1) {
                        $payment_type = 'Offline';
                    } elseif ($row->payment_type == 2) {
                        $payment_type = 'Paypal';
                    } else {
                        $payment_type = 'Add User Wallet';
                    }
                    return $payment_type;
                })
                ->addColumn('action', function ($row) {
                    return view('affiliate::withdraw.components._action', ['row' => $row]);
                })
                ->rawColumns(['action'])
                ->toJson();
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function confirmWithdraw($id)
    {
        try {
            DB::beginTransaction();
            $this->affiliateTransactionRepo->withdrawConfirm($id);
            DB::commit();
            return response()->json(['status' => 200]);
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function completeWithdraw()
    {
        try {
            return view('affiliate::withdraw.complete_withdraw');
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    public function completeWithdrawDatatable(Request $request)
    {
        try {
            $data['start_date'] = '';
            $data['end_date'] = '';
            if ($request->filter_date) {
                $date = explode('-', $request->filter_date);
                $data['start_date'] = date('Y-m-d', strtotime($date[0]));
                $data['end_date'] = date('Y-m-d', strtotime($date[1]));
            }
            $data = $this->affiliateTransactionRepo->confirmWithdrawQuery($data['start_date'], $data['end_date']);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('request_date', function ($row) {
                    return showDate($row->request_date);
                })
                ->editColumn('confirm_date', function ($row) {
                    return showDate($row->confirm_date);
                })
                ->addColumn('confirmedUser', function ($row) {
                    return $row->confirmedUser->name;
                })
                ->addColumn('amount', function ($row) {
                    return getPriceFormat($row->withdraw_amount);
                })
                ->addColumn('payment_type', function ($row) {
                    if ($row->payment_type == 1) {
                        $payment_type = 'Offline';
                    } elseif ($row->payment_type == 2) {
                        $payment_type = 'Paypal';
                    } else {
                        $payment_type = 'Add User Wallet';
                    }
                    return $payment_type;
                })

                ->rawColumns(['action'])
                ->toJson();
        } catch (Exception $e) {
            Toastr::error($e->getMessage(), trans('common.Error'));
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }


    public function commission()
    {
        return view('affiliate::withdraw.commission');
    }

    public function commissionData(Request $request)
    {

        $startDate = '';
        $endDate = '';
        if ($request->filter_date) {
            $date = explode('-', $request->filter_date);
            $startDate = date('Y-m-d', strtotime($date[0]));
            $endDate = date('Y-m-d', strtotime($date[1]));
        }
        $query = AffiliateReferralPayment::with(['course', 'incomeFrom']);
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        $query->latest();
        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('date', function ($row) {
                return showDate($row->date);
            })
            ->editColumn('amount', function ($row) {
                return getPriceFormat($row->amount);
            })
            ->addColumn('title', function ($row) {
                return $row?->course?->title;
            })
            ->addColumn('user_name', function ($row) {
                return $row?->incomeFrom?->name;
            })

            ->addColumn('status', function ($row) {
                $status = $row->status;
                if ($status == 1) {
                    return '<span class="badge_1">' . __('affiliate.Done') . '</span>';
                } else {
                    return '<span class="badge_3">' . __('affiliate.Pending') . '</span>';
                }
            })
            ->rawColumns(['status'])
            ->toJson();
    }
    public function pendingCommissionApproved()
    {
        try {
            DB::beginTransaction();
            Artisan::call('affiliate:commission');
            DB::commit();
            Toastr::success(__('affiliate.Pending Commissions Approved Successfully'));
            return back();
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), trans('common.Error'));
            return $e->getMessage();
        }
    }
}
