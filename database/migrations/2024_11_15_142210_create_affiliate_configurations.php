<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('affiliate_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
            $table->integer('lms_id')->default(1);
            $table->timestamps();
        });

        $config_data = [

            [
                'key' => 'min_withdraw',
                'value' => 10 //currency unit
            ],
            [
                'key' => 'commission_type',
                'value' => "Percentage"//Percentage,Flat
            ],
            [
                'key' => 'commission_amount',
                'value' => 10
            ],
            [
                'key' => 'balance_add_account_after_days',
                'value' => 10 //in days
            ],
            [
                'key' => 'transfer_approval_need',
                'value' => 1
            ],
            [
                'key' => 'referral_duration_type',
                'value' => 'Fixed' //Fixed,Lifetime
            ],

            [
                'key' => 'referral_duration',
                'value' => 180 //in days
            ],
        ];
        DB::table('affiliate_configurations')->insert($config_data);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_configurations');
    }
};
