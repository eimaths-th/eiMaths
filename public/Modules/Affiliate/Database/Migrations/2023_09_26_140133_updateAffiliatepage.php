<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\FrontendManage\Entities\FrontPage;

class UpdateAffiliatepage extends Migration
{
    public function up()
    {
        $page = FrontPage::where('slug', '/affiliate')->first();
        if (!$page) {
            $page = new FrontPage();
        }
        $page->name = 'Affiliate';
        $page->title = 'Affiliate';
        $page->sub_title = 'Affiliate';
        $page->details = $this->page();
        $page->slug = '/affiliate';
        $page->status = 1;
        $page->is_static = 1;
        $page->save();
    }


    public function down()
    {
        //
    }

    public function page()
    {

        $html = '<div
    class="full-page"
    data-type="component-text"
    data-preview=""
    data-aoraeditor-title="All Section" data-aoraeditor-categories="Affiliate Page">

    <div class="row">
        <div class="col-sm-12 ui-resizable" data-type="container-content">';
        $html .= view(theme('snippets.components._affiliate_title'))->render();
        $html .= ' </div>
    </div>
    <div class="row">
        <div class="col-sm-12 ui-resizable" data-type="container-content">';
        $html .= view(theme('snippets.components._affiliate_commission_section'))->render();
        $html .= ' </div>
    </div>
    <div class="row">
        <div class="col-sm-12 ui-resizable" data-type="container-content">';
        $html .= view(theme('snippets.components._affiliate_how_work_section'))->render();
        $html .= ' </div>
    </div>
    <div class="row">
        <div class="col-sm-12 ui-resizable" data-type="container-content">';
        $html .= view(theme('snippets.components._affiliate_rules_section'))->render();
        $html .= ' </div>
    </div>
    <div class="row">
        <div class="col-sm-12 ui-resizable" data-type="container-content">';
        $html .= view(theme('snippets.components._affiliate_benifit_section'))->render();
        $html .= ' </div>
    </div>
    <div class="row">
        <div class="col-sm-12 ui-resizable" data-type="container-content">';
        $html .= view(theme('snippets.components._affiliate_frequntlya_ask_section'))->render();
        $html .= ' </div>
    </div>
</div>';
        return $html;
    }
}
