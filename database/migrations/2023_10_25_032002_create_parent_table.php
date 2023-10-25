<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('centre_id');
            $table->string('fname');
            $table->string('lname');
            $table->string('telp')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->longText('address')->nullable();
            $table->string('relationship')->nullable();
            $table->string('gender')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parents');
    }
}
