<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parent_registration_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('phone', 32);
            $table->string('relationship', 16);
            $table->json('student_ids');
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
        // Copy only valid references, preserving the legacy value for reconciliation.
        DB::table('parents')->orderBy('id')->chunkById(200, function ($parents) {
            foreach ($parents as $parent) {
                $ids = array_values(array_unique(array_filter(explode(',', (string) $parent->student_id), fn ($id) => ctype_digit($id))));
                foreach (DB::table('contacts')->whereIn('id', $ids)->pluck('id') as $studentId) {
                    if (!DB::table('parent_student')->where('parent_id', $parent->id)->where('student_id', $studentId)->exists()) {
                        DB::table('parent_student')->insert(['parent_id' => $parent->id, 'student_id' => $studentId, 'created_at' => now(), 'updated_at' => now()]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_registration_challenges');
        // Parent/student links are business data and are intentionally preserved.
    }
};
