<?php

use Illuminate\Database\Migrations\Migration;

class AddCommissionListInAffiliate extends Migration
{
    public function up()
    {
        $routes = [
             ['name' => 'Commission List', 'route' => 'affiliate.commission', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1, 'module' => 'Affiliate'],


        ];
        if (function_exists('permissionUpdateOrCreate')) {
            permissionUpdateOrCreate($routes);
        }
    }

    public function down()
    {
        //
    }
}
