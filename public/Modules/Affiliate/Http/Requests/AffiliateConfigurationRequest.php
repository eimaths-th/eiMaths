<?php

namespace Modules\Affiliate\Http\Requests;

use App\Traits\ValidationMessage;
use Illuminate\Foundation\Http\FormRequest;

class AffiliateConfigurationRequest extends FormRequest
{

    use ValidationMessage;

    public function rules()
    {
        return [
            'min_withdraw'=>'required',
             'commission_amount'=>'required',
             'balance_add_account_after_days'=>'required',
             'referral_duration'=>'required_if:referral_duration_type,Fixed',
        ];

    }

    public function authorize()
    {
        return true;
    }
}
