<?php

use Illuminate\Database\Migrations\Migration;

class AddPusherSettingPermission extends Migration
{
    public function up()
    {
        $routes = [
            ['name' => 'Pusher Setting', 'route' => 'pusher.setting', 'type' => 2, 'parent_route' => 'settings'],
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
