<?php


namespace Modules\Affiliate\Repositories;


use App\User;
use Illuminate\Support\Facades\Auth;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Modules\Affiliate\Entities\AffiliateWithdraw;

class AffiliateTransactionRepository
{
    public function withdrawRequest(array $data)
    {
        $withdraw = AffiliateWithdraw::create([
            'user_id'=>$data['user_id'],
            'withdraw_amount'=>$data['withdraw_amount'],
            'payment_type'=>$data['payment_type'],
            'request_date'=>date('Y-m-d'),
        ]);
        if($withdraw){
            $user_wallet = AffiliateUserWallet::where('user_id',$data['user_id'])->first();
            if($user_wallet){
                // $update_wallet_amount = $user_wallet->amount - $data['withdraw_amount'];
                $update_wallet_amount = $user_wallet->amount;
                $user_wallet->update(['amount' => $update_wallet_amount]);
            }
        }
        return $withdraw;
    }

    public function balanceTransferToWallet(array $data)
    {

        if(affiliateConfig('transfer_approval_need') == 0){
            $withdraw = AffiliateWithdraw::create([
                'user_id'=>$data['user_id'],
                'withdraw_amount'=>$data['transfer_amount'],
                'payment_type'=>$data['payment_type'],
                'request_date'=>date('Y-m-d'),
                'confirm_date' => date('Y-m-d'),
                'status'=>1,
            ]);
            if($withdraw){
                $user_wallet = AffiliateUserWallet::where('user_id',$data['user_id'])->first();
                if($user_wallet){
                    $update_wallet_amount = $user_wallet->amount - $data['transfer_amount'];
                    $user_wallet->update(['amount' => $update_wallet_amount]);
                }
            }
            $user = User::where('id',$data['user_id'])->first();
            if($user){
                $newBalance = $user->balance + $data['transfer_amount'];
                $user->update(['balance'=>$newBalance]);
            }
            return $withdraw;

        }else{
            $withdraw = AffiliateWithdraw::create([
                'user_id'=>$data['user_id'],
                'withdraw_amount'=>$data['transfer_amount'],
                'payment_type'=>$data['payment_type'],
                'request_date'=>date('Y-m-d'),
            ]);
            if($withdraw){
                $user_wallet = AffiliateUserWallet::where('user_id',$data['user_id'])->first();
                if($user_wallet){
                    $update_wallet_amount = $user_wallet->amount - $data['transfer_amount'];
                    $user_wallet->update(['amount' => $update_wallet_amount]);
                }
            }
            return $withdraw;
        }

    }

    public function find($id)
    {
        return AffiliateWithdraw::with(['user','confirmedUser'])->findOrFail($id);
    }

    public function delete($id)
    {
        $row = $this->find($id);
        if($row){
            $user_wallet = AffiliateUserWallet::where('user_id',$row->user_id)->first();
            if($user_wallet){
                $update_wallet_amount = $user_wallet->amount + $row->withdraw_amount;
                $user_wallet->update(['amount' => $update_wallet_amount]);
            }
            $row->delete();
        }
        return true;

    }

    public function update(array $data,$id)
    {
        $row = $this->find($id);
        if($row->payment_type == 3){
            $newAmount = $data['transfer_amount'];
        }else{
            $newAmount =$data['withdraw_amount'];
        }

        if($row){
            $user_wallet = AffiliateUserWallet::where('user_id',$row->user_id)->first();
            if($user_wallet){
                $update_wallet_amount = $user_wallet->amount + $row->withdraw_amount - $newAmount;
                $user_wallet->update(['amount' => $update_wallet_amount]);
            }
        }

        $row->update([
            'withdraw_amount'=>$newAmount,
            'payment_type'=>$data['payment_type'],
        ]);

        return true;

    }

    public function userWiseWithdraw($startDate = null ,$endDate = null)
    {
       $user = Auth::user();
       if($user->hasRole('Affiliate-user'))
       {
           if($startDate && $endDate){
               return AffiliateWithdraw::with('user')->where('user_id',Auth::id())->whereBetween('request_date', [$startDate, $endDate])->orderBy('id','DESC')->get();
           }else{
               return AffiliateWithdraw::with('user')->where('user_id',Auth::id())->orderBy('id','DESC')->get();
           }
       }else{
           if($startDate && $endDate){
               return AffiliateWithdraw::with('user')->where('user_id',Auth::id())->whereBetween('request_date', [$startDate, $endDate])->orderBy('id','DESC')->get();
           }else{
               return AffiliateWithdraw::with('user')->where('user_id',Auth::id())->orderBy('id','DESC')->get();
           }
       }
    }

    public function userWiseIncome($startDate = null ,$endDate = null)
    {
        $user = Auth::user();

        if($user->hasRole('Affiliate-user'))
        {
            if($startDate && $endDate){
                return AffiliateReferralPayment::with(['course','incomeFrom'])->where('payment_to',Auth::id())->whereBetween('date', [$startDate, $endDate])->orderBy('id','DESC')->get();
            }else{
                return AffiliateReferralPayment::with(['course','incomeFrom'])->where('payment_to',Auth::id())->orderBy('id','DESC')->get();
            }
        }else{
            if($startDate && $endDate){
                return AffiliateReferralPayment::with(['course','incomeFrom'])->where('payment_to',Auth::id())->whereBetween('date', [$startDate, $endDate])->orderBy('id','DESC')->get();
            }else{
                return AffiliateReferralPayment::with(['course','incomeFrom'])->where('payment_to',Auth::id())->orderBy('id','DESC')->get();
            }
        }


    }

    public function pendingWithdraw()
    {
        return AffiliateWithdraw::with(['user'])->where('status',0)->orderBy('request_date','asc')->get();
    }
    public function pendingWithdrawQuery($startDate = null,$endDate = null)
    { 
        if($startDate && $endDate){
            return AffiliateWithdraw::with(['user'])->where('status',0)->whereBetween('request_date', [$startDate, $endDate])->orderBy('request_date','asc');
        }else{
            return AffiliateWithdraw::with(['user'])->where('status',0)->orderBy('request_date','asc');
        }
    }

    public function confirmWithdraw()
    {
        return AffiliateWithdraw::with(['user','confirmedUser'])->where('status',1)->orderBy('confirm_date','asc')->get();
    }

    public function confirmWithdrawQuery($startDate = null,$endDate = null)
    {

        if($startDate && $endDate){
            return AffiliateWithdraw::with('user','confirmedUser')->where('affiliate_withdraws.status',1)->whereBetween('confirm_date', [$startDate, $endDate])->select('affiliate_withdraws.*');
        }else{
            return AffiliateWithdraw::with('user','confirmedUser')->where('affiliate_withdraws.status',1)->select('affiliate_withdraws.*');
        }
    }


    public function withdrawConfirm($id)
    {
        $row = $this->find($id);
        if($row->payment_type == 3){
           $user = User::where('id',$row->user_id)->first();
           if($user){
               $newBalance = $user->balance + $row->withdraw_amount;
               $user->update(['balance'=>$newBalance]);
           }
        }
        return $row->update([
            'status' => 1,
            'confirm_date' => date('Y-m-d'),
            'confirmed_by' => Auth::id(),
        ]);

    }


}
