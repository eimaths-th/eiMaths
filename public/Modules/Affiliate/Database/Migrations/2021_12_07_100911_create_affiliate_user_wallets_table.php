<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateUserWalletsTable extends Migration
{


    public function up()
    {
        Schema::create('affiliate_user_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->double('amount')->default(0);
            $table->string('paypal_account')->nullable();
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->integer('lms_id')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('affiliate_user_wallets');
    }
}
