<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\level;
class levelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        level::create([
            'level_type' => 1,
            'name' => 'K1',
            'full_name' => 'Kindergarten',
            'status' => 1,
            'price' => 4000.00,
            'half_price' => 2000.00,
            'book_price' => 2900.00,
            'book_half_price' => 1450.00
        ]);
        level::create([
            'level_type' => 1,
            'name' => 'K2',
            'full_name' => 'Kindergarten',
            'status' => 1,
            'price' => 4000.00,
            'half_price' => 2000.00,
            'book_price' => 2900.00,
            'book_half_price' => 1450.00
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P1',
            'full_name' => 'Primary school',
            'status' => 1,
            'price' => 7000.00,
            'half_price' => 3500.00,
            'book_price' => 3500.00,
            'book_half_price' => 1750.00
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P2',
            'full_name' => 'Primary school',
            'status' => 1,
            'price' => 7000.00,
            'half_price' => 3500.00,
            'book_price' => 3500.00,
            'book_half_price' => 1750.00
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P3',
            'full_name' => 'Primary school',
            'status' => 1,
            'price' => 7000.00,
            'half_price' => 3500.00,
            'book_price' => 3500.00,
            'book_half_price' => 1750.00
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P4',
            'full_name' => 'Primary school',
            'status' => 1,
            'price' => 10000.00,
            'half_price' => 5000.00,
            'book_price' => 5000.00,
            'book_half_price' => 2500.00
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P5',
            'full_name' => 'Primary school',
            'status' => 1,
            'price' => 10000.00,
            'half_price' => 5000.00,
            'book_price' => 5000.00,
            'book_half_price' => 2500.00
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P6',
            'full_name' => 'Primary school',
            'status' => 1,
            'price' => 10000.00,
            'half_price' => 5000.00,
            'book_price' => 5000.00,
            'book_half_price' => 2500.00
        ]);
    }
}
