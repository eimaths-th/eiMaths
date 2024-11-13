<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateLinksTable extends Migration
{

    public function up()
    {
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->string('affiliate_link', 500);
            $table->unsignedBigInteger('owner_id');
            $table->integer('visits')->default(0);
            $table->integer('registered')->default(0);
            $table->integer('purchased')->default(0);
            $table->integer('commissions')->default(0);
            $table->integer('lms_id')->default(1);
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('affiliate_links');
    }
}
