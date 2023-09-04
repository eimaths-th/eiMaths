<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //Permission::truncate();

        $permissions = [
            'contact-list',
            'contact-create',
            'contact-edit',
            'contact-delete',
            'external-doc-list',
            'external-doc-create',
            'external-doc-edit',
            'external-doc-delete',
            'master-data-list',
            'master-data-create',
            'master-data-edit',
            'master-data-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'all-centre'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
