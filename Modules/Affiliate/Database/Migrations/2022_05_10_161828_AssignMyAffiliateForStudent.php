<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\Permission;
use Modules\RolePermission\Entities\RolePermission;

class AssignMyAffiliateForStudent extends Migration
{

    public function up()
    {
        $permission = Permission::where('route', 'affiliate.my_affiliate.index')->where('backend', '0')->first();
        if ($permission) {
            $role_permission = new RolePermission();
            $role_permission->permission_id = $permission->id;
            $role_permission->role_id = 3;
            $role_permission->save();
        }

    }

    public function down()
    {
        //
    }
}
