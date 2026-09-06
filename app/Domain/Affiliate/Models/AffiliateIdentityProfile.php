<?php

namespace App\Domain\Affiliate\Models;

use App\Domain\Affiliate\Enums\AffiliateIdentityStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateIdentityProfile extends Model
{
    protected $fillable = [
        'affiliate_profile_id',
        'honorific',
        'legal_first_name',
        'legal_last_name',
        'national_id',
        'national_id_hash',
        'card_expires_on',
        'address_line1',
        'subdistrict',
        'district',
        'province',
        'postal_code',
        'id_document_path',
        'id_document_mime',
        'id_document_size',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'terms_version',
        'terms_accepted_at',
        'privacy_version',
        'privacy_acknowledged_at',
        'marketing_consent',
        'marketing_consent_at',
        'registration_ip_hash',
        'registration_user_agent',
    ];

    protected $casts = [
        'national_id' => 'encrypted',
        'card_expires_on' => 'date',
        'status' => AffiliateIdentityStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'privacy_acknowledged_at' => 'datetime',
        'marketing_consent' => 'boolean',
        'marketing_consent_at' => 'datetime',
    ];

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function ReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function SensitiveHash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    public function MaskedNationalId(): string
    {
        $national_id = preg_replace('/\D+/', '', (string) $this->national_id);

        return strlen($national_id) === 13
            ? substr($national_id, 0, 1) . '-****-*****-' . substr($national_id, -2)
            : '—';
    }
}
