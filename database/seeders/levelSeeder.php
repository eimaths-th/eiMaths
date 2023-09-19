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
            'status' => 1,
            'price' => 525.00,
            'half_price' => 287.00,
            'book_price' => 12.50,
            'book_half_price' => 6
        ]);
        level::create([
            'level_type' => 1,
            'name' => 'K2',
            'status' => 1,
            'price' => 525.00,
            'half_price' => 287.00,
            'book_price' => 12.50,
            'book_half_price' => 6
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P1',
            'status' => 1,
            'price' => 525.00,
            'half_price' => 287.00,
            'book_price' => 12.50,
            'book_half_price' => 6
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P2',
            'status' => 1,
            'price' => 525.00,
            'half_price' => 287.00,
            'book_price' => 12.50,
            'book_half_price' => 6
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P3',
            'status' => 1,
            'price' => 575.00,
            'half_price' => 314.00,
            'book_price' => 12.50,
            'book_half_price' => 6
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P4',
            'status' => 1,
            'price' => 575.00,
            'half_price' => 314.00,
            'book_price' => 10.50,
            'book_half_price' => 6
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P5',
            'status' => 1,
            'price' => 595.00,
            'half_price' => 325.00,
            'book_price' => 10.50,
            'book_half_price' => 6
        ]);
        level::create([
            'level_type' => 2,
            'name' => 'P6',
            'status' => 1,
            'price' => 595.00,
            'half_price' => 325.00,
            'book_price' => 10.50,
            'book_half_price' => 6
        ]);
    }
}
