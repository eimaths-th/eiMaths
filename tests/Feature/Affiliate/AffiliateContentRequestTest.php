<?php

namespace Tests\Feature\Affiliate;

use App\Http\Requests\Affiliate\StoreAffiliateContentRequest;
use App\Http\Requests\Affiliate\ToggleAffiliateContentStatusRequest;
use App\Http\Requests\Affiliate\UpdateAffiliateContentRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateContentRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.allowed_destination_hosts', ['example.com']);
    }

    #[Test]
    public function StoreRequestRejectsInvalidCampaignAndMismatchedMediaType(): void
    {
        $request = StoreAffiliateContentRequest::create('/affiliate/media', 'POST', [
            'title' => 'Campaign',
            'type' => 'video',
            'destination_target' => '/courses/math',
            'campaign_code' => 'Bad Campaign',
        ]);
        $request->files->set('file', UploadedFile::fake()->image('campaign.png'));

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('campaign_code'));
        $this->assertTrue($validator->errors()->has('file'));
    }

    #[Test]
    public function UpdateRequestProhibitsCampaignAndFileReplacement(): void
    {
        $request = UpdateAffiliateContentRequest::create('/affiliate/media/1', 'PUT', [
            'campaign_code' => 'replacement-campaign',
            'file' => 'replacement.png',
        ]);
        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('campaign_code'));
        $this->assertTrue($validator->errors()->has('file'));
    }

    #[Test]
    public function ToggleRequestRequiresAnUnarchivedContentId(): void
    {
        $request = ToggleAffiliateContentStatusRequest::create('/affiliate/media/status', 'POST', []);
        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }
}
