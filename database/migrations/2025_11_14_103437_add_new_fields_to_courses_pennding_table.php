<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses_pennding', function (Blueprint $table) {
            // Check if columns don't exist before adding them

            $table->dateTime('appointment_date')->nullable()->after('telp');
            $table->string('student_name')->nullable()->after('appointment_date');
            $table->string('student_nickname')->nullable()->after('student_name');
            $table->string('grade')->nullable()->after('student_nickname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses_pennding', function (Blueprint $table) {
            // Drop columns if they exist
            $table->dropColumn('appointment_date');
            $table->dropColumn('student_name');
            $table->dropColumn('student_nickname');
            $table->dropColumn('grade');

        });
    }
};
