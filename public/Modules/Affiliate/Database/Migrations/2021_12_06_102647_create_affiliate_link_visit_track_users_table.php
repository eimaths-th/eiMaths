<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateLinkVisitTrackUsersTable extends Migration
{


    public function up()
    {
        Schema::create('affiliate_link_visit_track_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_link_id');
            $table->string('ip');
            $table->string('agent');
            $table->date('date');
            $table->foreign('affiliate_link_id')->on('affiliate_links')->references('id')->onDelete('cascade');
            $table->integer('lms_id')->default(1);
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('affiliate_link_visit_track_users');
    }
}
