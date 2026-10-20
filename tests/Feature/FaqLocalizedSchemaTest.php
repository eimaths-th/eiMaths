<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FaqLocalizedSchemaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function MetadataIsAdditiveAndActivationDefaultsOff(): void
    {
        $this->assertTrue(Schema::hasTable('faq_localized_slug_states'));
        $this->assertTrue(Schema::hasTable('faq_slug_routes'));
        $this->assertTrue(Schema::hasColumn('faqs', 'slug'));
        $this->assertFalse(config('faq-urls.enabled'));
        foreach (['faq_id', 'locale', 'last_revision', 'source_base', 'initialized_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('faq_localized_slug_states', $column));
        }
        foreach (['faq_id', 'locale', 'slug', 'revision', 'current_marker', 'retired_at', 'expires_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('faq_slug_routes', $column));
        }
    }
}
