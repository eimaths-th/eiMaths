<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('test_sys_questions', function (Blueprint $table) {
            $table->enum('answer_type', ['frac', 'mixed'])->default('frac')->after('answer_denominator');
        });
    }

    public function down()
    {
        Schema::table('test_sys_questions', function (Blueprint $table) {
            $table->dropColumn('answer_type');
        });
    }
};
