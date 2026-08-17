<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Services\AffiliatePreflightService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateProfileOwnershipMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const UNIQUE_INDEX = 'affiliate_profiles_user_id_unique';

    #[Test]
    public function DuplicateOwnersBlockTheMigrationWithoutDeletingRows(): void
    {
        $connection_name = 'affiliate_migration_test';
        $original_default = (string) config('database.default');
        Config::set("database.connections.{$connection_name}", [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        Config::set('database.default', $connection_name);
        DB::purge($connection_name);

        try {
            Schema::connection($connection_name)->create('affiliate_profiles', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('referral_code');
                $table->string('status', 32);
            });
            DB::connection($connection_name)->table('affiliate_profiles')->insert([
                ['user_id' => 7, 'referral_code' => 'AFF-HIDDEN-001', 'status' => 'ACTIVE'],
                ['user_id' => 7, 'referral_code' => 'AFF-HIDDEN-002', 'status' => 'PENDING_APPROVAL'],
            ]);

            $migration = require base_path(
                'database/migrations/2026_08_11_000005_add_unique_user_to_affiliate_profiles.php'
            );

            try {
                $migration->up();
                $this->fail('Expected duplicate Affiliate profile owners to block the migration.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('duplicate', strtolower($exception->getMessage()));
                $this->assertStringNotContainsString('AFF-HIDDEN', $exception->getMessage());
            }

            $this->assertSame(2, DB::connection($connection_name)->table('affiliate_profiles')->count());
            $this->assertFalse(
                Schema::connection($connection_name)->hasIndex('affiliate_profiles', self::UNIQUE_INDEX, 'unique')
            );
        } finally {
            DB::disconnect($connection_name);
            Config::set('database.default', $original_default);
            DB::purge($original_default);
        }
    }

    #[Test]
    public function PreflightPassesWithoutDuplicateOwners(): void
    {
        $check = $this->FindCheck(app(AffiliatePreflightService::class)->Checks(), 'affiliate_profile_ownership');

        $this->assertSame('PASS', $check['status']);
    }

    #[Test]
    public function PreflightBlocksDuplicateOwners(): void
    {
        $connection_name = 'affiliate_preflight_test';
        $original_default = (string) config('database.default');
        Config::set("database.connections.{$connection_name}", [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        Config::set('database.default', $connection_name);
        DB::purge($connection_name);

        try {
            Schema::connection($connection_name)->create('affiliate_profiles', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('referral_code');
                $table->string('status', 32);
            });
            DB::connection($connection_name)->table('affiliate_profiles')->insert([
                ['user_id' => 1, 'referral_code' => 'AFF-DUPLICATE-001', 'status' => 'ACTIVE'],
                ['user_id' => 1, 'referral_code' => 'AFF-DUPLICATE-002', 'status' => 'ACTIVE'],
            ]);

            $check = $this->FindCheck(
                app(AffiliatePreflightService::class)->Checks(),
                'affiliate_profile_ownership'
            );

            $this->assertSame('BLOCKED', $check['status']);
            $this->assertStringNotContainsString('AFF-DUPLICATE', $check['message']);
        } finally {
            DB::disconnect($connection_name);
            Config::set('database.default', $original_default);
            DB::purge($original_default);
        }
    }

    private function FindCheck(array $checks, string $name): array
    {
        foreach ($checks as $check) {
            if ($check['name'] === $name) {
                return $check;
            }
        }

        $this->fail("Missing preflight check: {$name}");
    }
}
