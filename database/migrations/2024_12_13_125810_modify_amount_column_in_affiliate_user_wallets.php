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
        Schema::table('affiliate_user_wallets', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0)->change();  // Modify the column to decimal with precision 10 and scale 2
        });
    }

    public function down()
    {
        Schema::table('affiliate_user_wallets', function (Blueprint $table) {
            $table->double('amount')->default(0)->change();  // Revert to double if you need to roll back
        });
    }
};
