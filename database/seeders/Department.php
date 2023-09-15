<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department as Dept;

class Department extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Dept::create([
            'name' => 'HQ',
            'code' => 'HQ',
            'status' => 1
        ]);
        Dept::create([
            'name' => 'Ratchaphruek',
            'code' => 'RP',
            'status' => 1
        ]);
        Dept::create([
            'name' => 'Bangkae',
            'code' => 'BK',
            'status' => 1
        ]);

    }
}
