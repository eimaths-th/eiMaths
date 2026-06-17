<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_leads')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => $this->alterMySql(nullable: true),
            'sqlite' => null,
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_leads')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => $this->alterMySql(nullable: false),
            'sqlite' => null,
            default => null,
        };
    }

    private function alterMySql(bool $nullable): void
    {
        $null = $nullable ? 'NULL' : 'NOT NULL';

        foreach (['parent_name', 'phone', 'student_name'] as $column) {
            DB::statement("ALTER TABLE crm_leads MODIFY {$column} VARCHAR(255) {$null}");
        }

        DB::statement("ALTER TABLE crm_leads MODIFY student_grade VARCHAR(100) {$null}");
    }
};
