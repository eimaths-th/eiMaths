<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\FrontendManage\Entities\FrontPage;
use Modules\FrontendManage\Entities\HeaderMenu;
use Modules\RolePermission\Entities\Permission;

class AddAffiliatePageToFrontendPage extends Migration
{

    public function up()
    {
        Permission::where('route', 'affiliate.frontend.edit')->firstOrCreate(
            ['name' => 'Page Design', 'route' => 'affiliate.frontend.edit', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1]
        );

        Permission::where('route', 'affiliate.users')->firstOrCreate(
            ['name' => 'Users', 'route' => 'affiliate.users', 'parent_route' => 'affiliate', 'type' => 2, 'backend' => 1]
        );

        Permission::where('route', 'affiliate.users.index')->firstOrCreate(
            ['name' => 'List', 'route' => 'affiliate.users.index', 'parent_route' => 'affiliate.users', 'type' => 3, 'backend' => 1]
        );

        Permission::where('route', 'affiliate.users.approved')->firstOrCreate(
            ['name' => 'Approved', 'route' => 'affiliate.users.approved', 'parent_route' => 'affiliate.users', 'type' => 3, 'backend' => 1]
        );


        $page = FrontPage::where('slug', '/affiliate')->first();
        if (!$page) {
            $page = new FrontPage();
        }
        $page->name = 'Affiliate';
        $page->title = 'Affiliate';
        $page->sub_title = 'Affiliate';
        $page->details = null;
        $page->slug = '/affiliate';
        $page->status = 1;
        $page->is_static = 1;
        $page->save();

        $header_menu = HeaderMenu::where('link', 'affiliate')->first();
        if ($page && $header_menu == null) {
            $menu = new HeaderMenu();
            $menu->type = "Dynamic Page";
            $menu->element_id = $page->id;
            $menu->title = $page->title;
            $menu->link = '/affiliate';
            $menu->position = 10;
            $menu->save();

        }
    }


    public function down()
    {
        //
    }


}
