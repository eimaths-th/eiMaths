<?php

namespace Modules\Affiliate\Listeners;

use Modules\Affiliate\Entities\AffiliateLink;
use Modules\Affiliate\Entities\AffiliateLinkVisitTrackUser;
use Request;
use Browser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Affiliate\Events\CheckAffiliateLink;

class CheckAffiliateLinkListener
{

    public function __construct()
    {
        //
    }


    public function handle(CheckAffiliateLink $event)
    {
        $data = [];
        $url = urldecode(url()->full());
        $data['affiliate_link_id'] = 0;
        $affiliate_link = AffiliateLink::where('affiliate_link', $url)->first();
        if ($affiliate_link) {
            $data['affiliate_link_id'] = $affiliate_link->id;
        } else {
            return true;
        }
        $data['ip'] = Request::ip();
//        $data['agent'] = Browser::browserFamily().'-'.Browser::browserVersion().'-'.Browser::browserEngine().'-'.Browser::platformName().'-'.Browser::deviceModel();
        $data['agent'] = Browser::browserFamily() . '-' . Browser::platformName() . '-' . Browser::deviceModel();
        $flag = $this->checkVisitRecord($data);
        if (!empty($data['affiliate_link_id']) && $flag) {
            $visitRecord = $this->linkVisitTrack($data);
            if ($visitRecord) {
                $affiliate_link->update(['visits' => $affiliate_link->visits + 1]);
            }
        }

    }

    private function linkVisitTrack($data)
    {
        return AffiliateLinkVisitTrackUser::create([
            'affiliate_link_id' => (int)$data['affiliate_link_id'],
            'ip' => $data['ip'],
            'agent' => $data['agent'],
            'date' => date('Y-m-d'),
        ]);
    }

    private function checkVisitRecord($data)
    {
        $row = AffiliateLinkVisitTrackUser::where('affiliate_link_id', $data['affiliate_link_id'])->where('ip', $data['ip'])->where('agent', $data['agent'])->first();
        if ($row) {
            return false;
        } else {
            return true;
        }
    }
}
