<?php


namespace Modules\Affiliate\Repositories;

use App\Models\AffiliateUserBank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Affiliate\Entities\AffiliateConfiguration;
use Modules\Affiliate\Entities\AffiliateLink;
use Modules\Affiliate\Entities\AffiliateLinkVisitTrackUser;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Modules\Affiliate\Entities\ReferralUser;
use Request;
use Browser;

class AffiliateRepository
{
    public function all()
    {
        $user = Auth::user();
        if ($user->hasRole('Affiliate-user')) {
            // dd($user);
            // return AffiliateLink::with(['owner','registerUser','payment'])->where('owner_id',Auth::id())->paginate(10);
            return AffiliateLink::with(['owner', 'registerUser', 'payment'])
                ->where('owner_id', Auth::id())->get();
            // return 'true';
        } else {

            return AffiliateLink::with(['owner', 'registerUser', 'payment'])->where('owner_id', Auth::id())->get();
            // return 'f';

        }
    }
    public function firstCreate(array $data)
    {
        return AffiliateLink::create([
            'affiliate_link' => $data['affiliate_link'],
            'owner_id' => $data['user_id'],
        ]);
    }
    public function create(array $data)
    {
        return AffiliateLink::create([
            'affiliate_link' => $data['affiliate_link'],
            'owner_id' => Auth::id(),
        ]);
    }

    public function affiliateUser($userId)
    {
        if ($this->checkVisitRecord()) {
            return ReferralUser::create([
                'user_id' => $userId,
                'affiliate_link_id' => $this->checkVisitRecord()->affiliate_link_id,
                'validity_start_date' => $this->checkVisitRecord()->date,
            ]);
        } else {
            return false;
        }
    }

    private function checkVisitRecord()
    {
        $ip = Request::ip();
        $agent = Browser::browserFamily() . '-' . Browser::platformName() . '-' . Browser::deviceModel();
        $row = AffiliateLinkVisitTrackUser::where('ip', $ip)->where('agent', $agent)->orderBy('id', 'DESC')->first();
        if ($row) {
            return $row;
        } else {
            return false;
        }
    }

    public function configuration(array $data)
    {
        foreach ($data as $key => $value) {
            $row = AffiliateConfiguration::where('key', $key)->first();
            if ($row) {
                $row->update(['value' => $value]);
            } else {
                AffiliateConfiguration::create([
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }

        Cache::forget('affiliate_config_' . SaasDomain());

        $datas = [];
        foreach (\Modules\Affiliate\Entities\AffiliateConfiguration::get() as  $setting) {
            $datas[$setting->key] = $setting->value;
        }
        Cache::rememberForever('affiliate_config_' . SaasDomain(), function () use ($datas) {
            return $datas;
        });

        return true;
    }

    public function addOrUpdatePaypalAccountUser(array $data)
    {

        return AffiliateUserWallet::updateOrCreate(
            [
                'user_id'   => Auth::id(),
            ],
            [
                'paypal_account'     => $data['paypal_account'],
            ]
        );
    }
    public function addOrUpdateBankAccount(array $data)
    {
        // dd($data);

        // AffiliateUserWallet::updateOrCreate(
        //     [
        //         'user_id'   => Auth::id(),
        //     ],
        //     [
        //         'paypal_account'     => 0,
        //     ]
        // );
        return AffiliateUserBank::updateOrCreate(
            [
                'user_id'   => Auth::id(),
            ],
            [
                'bank_name'     => $data['bank_name'],
                'account_number'     => $data['account_number'],
                'account_name'     => $data['account_name'],
            ]
        );
    }
}
