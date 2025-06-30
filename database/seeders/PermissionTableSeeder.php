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
            /* 'student-list',
            'student-create',
            'student-edit',
            'student-delete',
            'product-list',
            'product-show',
            'product-create',
            'product-edit',
            'product-delete',
            'order-list',
            'order-show',
            'order-create',
            'order-edit',
            'order-delete',
            'invoice-list',
            'invoice-show',
            'invoice-create',
            'invoice-edit',
            'invoice-delete',
            'receipt-list',
            'receipt-show',
            'receipt-create',
            'receipt-edit',
            'receipt-delete',
            'centre-manage',
            'centre-create',
            'centre-edit',
            'centre-delete', */
            'department-list',
            'department-create',
            'department-edit',
            'department-delete',
            /* 'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'blog-list',
            'blog-create',
            'blog-edit',
            'blog-delete',
            'all-centre', */
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
