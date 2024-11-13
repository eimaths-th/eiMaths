<?php

namespace Modules\FrontendManage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HeaderFooterStyleController extends Controller
{

    public function index()
    {
        return view('frontendmanage::header_footer_style.index');
    }

    public function store(Request $request)
    {
        //
    }
}
