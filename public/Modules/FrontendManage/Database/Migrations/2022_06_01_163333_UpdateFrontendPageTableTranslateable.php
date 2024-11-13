<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFrontendPageTableTranslateable extends Migration
{

    public function up()
    {


        $lang_code = 'en';
        $table_name = 'front_pages';

        Schema::table($table_name, function ($table) {
            $table->longText('title')->nullable()->change();
            $table->longText('sub_title')->nullable()->change();
            $table->longText('details')->nullable()->change();
        });
//        DB::statement('ALTER TABLE `' . $table_name . '`
//    CHANGE `title` `title` LONGTEXT  NULL DEFAULT NULL,
//    CHANGE `sub_title` `sub_title` LONGTEXT  NULL DEFAULT NULL,
//    CHANGE `details` `details` LONGTEXT  NULL DEFAULT NULL;
//
//    ');

//        $pages = \Modules\FrontendManage\Entities\FrontPage::where('is_static', 0)->get();
//        foreach ($pages as $page) {
//            $page->details = $this->container($page->details, $page->title, $page->subtitle);
//            $page->save();
//        }

        $pages = \Modules\FrontendManage\Entities\FrontPage::where('is_static', 0)->get();
        foreach ($pages as $page) {
            DB::table('front_pages')->where('id', $page->id)->update([
                'details' => $this->container($page->details, $page->title, $page->sub_title)
            ]);
        }
    }

    public function container($details, $title = '', $subtitle = '')
    {
        $imagePath = asset('frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg');

        $html = "
    <div class='row'>
        <div class='col-sm-12 ui-resizable' data-type='container-content'>
            <div class='breadcrumb_area bradcam_bg_1 position-relative'>
                <div class='breadcrumb_img w-100 h-100 position-absolute bottom-0 left-0'>
                    <img class='w-100 h-100 img-cover' src='" . $imagePath . "' alt=''>
                </div>
                <div class='container'>
                    <div class='row'>
                        <div class='col-lg-9 offset-lg-1'>
                            <div class='breadcam_wrap'>
                                <span>" . $title . "</span>
                                <h3>" . $subtitle . "</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='col-sm-12 ui-resizable' data-type='container-content'>
        <div class='courses_area'><div class='container'><div class='row justify-content-center'><div class='col-lg-12'>

            " . $details . "

            </div></div></div></div>
        </div>
    </div>";

        return $html;
    }

    public function down()
    {
        //
    }
}
