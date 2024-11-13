<?php

namespace Modules\Affiliate\Listeners;


use App\User;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Modules\Affiliate\Events\ReferralPayment;


class ReferralPaymentListener
{

    public function __construct()
    {
        //
    }


    public function handle(ReferralPayment $event)
    {
        $purchasePrice = $event->purchase_price;
        $user = User::with(['isReferralUser', 'isReferralUser.affiliateLink'])->find($event->user_id);
        if (affiliateConfig('commission_type') == 'Percentage') {
            $amount = (affiliateConfig('commission_amount') / 100) * $purchasePrice;
        } else {
            $amount = affiliateConfig('commission_amount');
        }
        $aff = '';
        $data = [
            'payment_to' => $user->isReferralUser->affiliateLink->owner_id,
            // 'amount' => number_format($amount, 2),
            // 'amount' => round($amount, 2),
            'amount' => $amount,
            'affiliate_link_id' => $user->isReferralUser->affiliate_link_id,
            'payment_from' => $user->id,
            'course_id' => $event->course_id,
            'date' => date('Y-m-d'),
        ];

        // $aff = AffiliateReferralPayment::create($data);
        // dd('dd1', $data, $amount, $purchasePrice, $aff);
        if (affiliateConfig('referral_duration_type') == 'Fixed') {
            $validity_end_date = Carbon::parse($user->isReferralUser->validity_start_date)->addDays(affiliateConfig('referral_duration'));
            $today = Carbon::now();
            if ($today->lte($validity_end_date)) {
                $aff = AffiliateReferralPayment::create($data);
                // dd('dd1', $data, $amount, $purchasePrice, $aff);
            }
            // dd('dd2');
        } elseif (affiliateConfig('referral_duration_type') == 'Onetime') {
            $onetime_flag = $this->checkAffiliateLinkPaymentAvailable($data);
            if ($onetime_flag) {
                $aff = AffiliateReferralPayment::create($data);
                // dd('dd3');
            }
            // dd('dd4');
        } else {
            $aff =  AffiliateReferralPayment::create($data);
            // dd('dd5');
        }
        // dd($data, $amount, $purchasePrice, $aff);
    }

    private function checkAffiliateLinkPaymentAvailable($data)
    {
        $payment = AffiliateReferralPayment::where('affiliate_link_id', $data['affiliate_link_id'])->first();
        if ($payment) {
            return false;
        } else {
            return true;
        }
    }
}
