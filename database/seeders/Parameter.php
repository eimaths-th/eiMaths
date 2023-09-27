<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Parameter as para;

class Parameter extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        para::create([
            'name' => 'Refundable Deposit',
            'name_th' => 'ค่ามัดจำ',
            'price' => 0.00,
        ]);
        para::create([
            'name' => 'Registration Fee',
            'name_th' => 'ค่าลงทะเบียนแรกเข้า',
            'price' => 750.00,
        ]);
        para::create([
            'name' => 'Accessories Fee',
            'name_th' => 'ค่าอุปกรณ์การเรียน',
            'price' => 500.00,
        ]);
        para::create([
            'name' => 'Bag Order Start Quantity',
            'name_th' => '',
            'price' => 21.00,
        ]);
        para::create([
            'name' => 'Bag Price',
            'name_th' => 'ค่ากระเป๋า',
            'price' => 17.00,
        ]);
    }
}
