<?php

namespace Modules\Affiliate\Events;

use Illuminate\Queue\SerializesModels;

class ReferralPayment
{
    use SerializesModels;

    public $user_id ,$course_id,$purchase_price;

    public function __construct($user_id,$course_id,$itemPrice)
    {
        $this->user_id = $user_id;
        $this->course_id = $course_id;
        $this->purchase_price = $itemPrice;
    }

    public function broadcastOn()
    {
        return [];
    }
}
