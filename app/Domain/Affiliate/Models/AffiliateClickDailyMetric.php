<?php

namespace App\Domain\Affiliate\Models;

use App\Models\AffiliateContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClickDailyMetric extends Model
{
    protected $table = 'affiliate_click_daily_metrics';

    protected $fillable = [
        'affiliate_profile_id',
        'metric_date',
        'dimension_key',
        'affiliate_content_id',
        'campaign_code',
        'total_clicks',
        'unique_clicks',
    ];

    protected $casts = [
        'affiliate_profile_id' => 'integer',
        'metric_date' => 'immutable_date',
        'affiliate_content_id' => 'integer',
        'total_clicks' => 'integer',
        'unique_clicks' => 'integer',
    ];

    public function AffiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class, 'affiliate_profile_id');
    }

    public function AffiliateContent(): BelongsTo
    {
        return $this->belongsTo(AffiliateContent::class, 'affiliate_content_id');
    }
}
