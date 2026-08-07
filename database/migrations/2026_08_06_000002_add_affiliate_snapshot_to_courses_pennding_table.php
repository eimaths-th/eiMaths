<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('courses_pennding')) {
            Schema::create('courses_pennding', function (Blueprint $table): void {
                $table->id();
                $table->string('ref')->nullable();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('telp')->nullable();
                $table->dateTime('appointment_date')->nullable();
                $table->string('student_name')->nullable();
                $table->string('student_nickname')->nullable();
                $table->string('grade')->nullable();
                $table->string('type_parent')->nullable();
                $table->string('course_name')->nullable();
                $table->string('status')->nullable();
                $table->string('start_course')->nullable();
                $table->string('to_course')->nullable();
                $table->string('start_term')->nullable();
                $table->string('to_term')->nullable();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->timestamps();
                $table->string('department_id')->nullable();
                $table->string('day')->nullable();
                $table->string('period')->nullable();
                $table->integer('price')->nullable();
                $table->string('slip')->nullable();
            });
        }

        Schema::table('courses_pennding', function (Blueprint $table): void {
            if (!Schema::hasColumn('courses_pennding', 'affiliate_profile_id')) {
                $table->foreignId('affiliate_profile_id')
                    ->nullable()
                    ->constrained('affiliate_profiles')
                    ->restrictOnDelete();
            }
            if (!Schema::hasColumn('courses_pennding', 'affiliate_captured_at')) {
                $table->timestamp('affiliate_captured_at')->nullable();
            }
            if (!Schema::hasColumn('courses_pennding', 'affiliate_expires_at')) {
                $table->timestamp('affiliate_expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('courses_pennding')) {
            return;
        }

        Schema::table('courses_pennding', function (Blueprint $table): void {
            if (Schema::hasColumn('courses_pennding', 'affiliate_profile_id')) {
                $table->dropForeign(['affiliate_profile_id']);
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('courses_pennding', 'affiliate_profile_id') ? 'affiliate_profile_id' : null,
                Schema::hasColumn('courses_pennding', 'affiliate_captured_at') ? 'affiliate_captured_at' : null,
                Schema::hasColumn('courses_pennding', 'affiliate_expires_at') ? 'affiliate_expires_at' : null,
            ]));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
