<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sterm;

class stermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sterm::create([
            'name' => '1',
            'status' => 1
        ]);
        Sterm::create([
            'name' => '2',
            'status' => 1
        ]);
        Sterm::create([
            'name' => '3',
            'status' => 1
        ]);
        Sterm::create([
            'name' => '4',
            'status' => 1
        ]);
    }
}
