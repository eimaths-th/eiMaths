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
            'price' => 150.00,
        ]);
        para::create([
            'name' => 'Registration Fee',
            'price' => 80.00,
        ]);
        para::create([
            'name' => 'Bag Order Start Quantity',
            'price' => 21.00,
        ]);
        para::create([
            'name' => 'Bag Price',
            'price' => 17.00,
        ]);
    }
}
