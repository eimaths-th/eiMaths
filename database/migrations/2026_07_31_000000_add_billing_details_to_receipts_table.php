<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->text('billing_details')->nullable()->after('student_name');
        });

        DB::table('receipts')
            ->where('receipt_number', 'RP2511010')
            ->whereNull('billing_details')
            ->update([
                'billing_details' => "บริษัท สำนักงานกฎหมาย ธันย์ธรณ์เทพ จำกัด\n"
                    . "เลขที่ 1055/525 สเตท ทาวเวอร์ ชั้น 28 ถนนสีลม แขวงสีลม เขตบางรัก กรุงเทพฯ 10500\n"
                    . 'เลขประจำตัวผู้เสียภาษีอากร : 0105566182291',
            ]);
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn('billing_details');
        });
    }
};
