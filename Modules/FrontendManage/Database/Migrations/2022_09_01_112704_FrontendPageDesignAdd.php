<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\FrontendManage\Entities\FrontPage;
use Modules\FrontendManage\Entities\PrivacyPolicy;

class FrontendPageDesignAdd extends Migration
{
    public function up()
    {
        $privacy = PrivacyPolicy::first();
        $frontend = FrontPage::where('slug', '/privacy')->first();
        $frontend->title = $privacy->page_banner_title;
        $frontend->sub_title = $privacy->page_banner_sub_title;
        $frontend->banner = $privacy->page_banner;
        $frontend->details = $this->container($privacy->details, $privacy->page_banner_title, $privacy->page_banner_sub_title);
        $frontend->save();
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

    }
}
