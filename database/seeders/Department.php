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
            'name' => 'Ratchaphruek',
            'code' => 'RJ',
            'status' => 1
        ]);

    }
}
