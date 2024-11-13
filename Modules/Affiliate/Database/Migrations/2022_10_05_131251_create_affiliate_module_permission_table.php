<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\Permission;

class CreateAffiliateModulePermissionTable extends Migration
{
    public function up()
    {
        Permission::where('route', 'affiliate.users')->delete();

        $routes = [
            ['name' => 'Affiliate', 'route' => 'affiliate', 'parent_route' => null, 'type' => 1, 'backend' => 1, 'module' => 'Affiliate'],
            ['name' => 'My Affiliate', 'route' => 'affiliate.my_affiliate.index', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1, 'module' => 'Affiliate'],

            //pending_withdraw
            ['name' => 'Pending List', 'route' => 'affiliate.pending_withdraw', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1, 'module' => 'Affiliate'],
            ['name' => 'Withdrawn', 'route' => 'affiliate.complete_withdraw', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1, 'module' => 'Affiliate'],

            ['name' => 'Confirm', 'route' => 'affiliate.confirm_withdraw', 'parent_route' => 'affiliate.pending_withdraw', 'type' => 3, 'backend' => 1, 'module' => 'Affiliate'],

            //configs
            ['name' => 'Configurations', 'route' => 'affiliate.configurations.update', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1, 'module' => 'Affiliate'],
            ['name' => 'Users', 'route' => 'affiliate.users.index', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1, 'module' => 'Affiliate'],
            ['name' => 'Page Design', 'route' => 'affiliate.frontend.edit', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1, 'module' => 'Affiliate'],

            //student permission
            ['name' => 'My Affiliate', 'route' => 'student.my_affiliate.index', 'parent_route' => null, 'type' => 1, 'backend' => 0, 'module' => 'Affiliate'],

        ];
        if (function_exists('permissionUpdateOrCreate')) {
            permissionUpdateOrCreate($routes);
        }


    }


    public function down()
    {

    }
}
