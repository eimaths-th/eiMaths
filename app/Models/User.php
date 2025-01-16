<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Modules\Affiliate\Entities\AffiliateWithdraw;
use Modules\RolePermission\Entities\Role;
use Spatie\Permission\Traits\HasRoles;
//use Spatie\Permission\Traits\HasPermissions;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'affiliate_request',
        'language_code',
        'referral',
        'email',
        'phone',
        'email_verified_at',
        'password',
        'department_id',
        'position_id',
        'disable',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


     /**
     * Get the department that the user belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position that the user holds.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    public function affiliateWallet()
    {
        return $this->hasOne(AffiliateUserWallet::class, 'user_id');
    }
    public function affiliateBank()
    {
        return $this->hasOne(AffiliateUserBank::class, 'user_id');
    }
   


    public function affiliateTransaction()
    {
        return $this->hasMany(AffiliateWithdraw::class, 'user_id');
    }

    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommissionList::class, 'payment_to');
    }
    public function affiliateCommissions2()
    {
        return $this->hasMany(AffiliateReferralPayment::class, 'payment_to');
    }

    public function isOrganization()
    {
        return $this->role_id == 5;
    }
    // public function roles()
    // {
    //     // Define the relationship with the Role model (assuming a many-to-many relationship)
    //     return $this->belongsToMany(Role::class);
    // }

    // public function hasRole($role)
    // {
    //     // Check if the user has the specified role
    //     return $this->roles()->where('name', $role)->exists();
    // }
}
