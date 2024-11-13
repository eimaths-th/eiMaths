<?php


namespace Modules\Affiliate\Repositories;


use App\User;

class AffiliateUserRepository
{
    public function all()
    {
        return User::where('affiliate_request', 1)->get();
    }

    public function query()
    {
        return User::where('affiliate_request', 1);
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function approved($id)
    {
        $row = $this->find($id);
        if ($row) {
            return $row->update(['accept_affiliate_request' => $row->accept_affiliate_request == 0 ? 1 : 0]);
        }
        return false;
    }

}
