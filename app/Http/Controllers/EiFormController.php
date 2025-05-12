<?php

namespace App\Http\Controllers;

use App\Models\EiForm;
use Illuminate\Http\Request;

class EiFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('eiform.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        // dd($request->all());
        EiForm::create($request->all());
        $thankYouMessage = "ขอบคุณสำหรับข้อมูล. <br/> เราจะติดต่อกลับโดยเร็วที่สุด";

        return response()->json([
            'html' => '<div class="et-pb-contact-message">
                            <p id="msg">' . $thankYouMessage . '</p>
                       </div>'
        ]);
    }

    public function backEndIndex(Request $request)
    {
        if ($request->ajax()) {
            //sleep(2);

            $datas = EiForm::all();
            
            // $datas->orderBy("id", "desc")->get();
            return datatables()->of($datas)
                
                ->editColumn('first_name_parent', function ($row) {
                    $text = '';
                    $fname = $row->first_name_parent ?? '';
                    $lname = $row->last_name_parent ?? '';
                    if ($fname != '' && $lname != '') {
                        $text = $fname . ' ' . $lname;
                    } elseif ($fname != '' && $lname == '') {
                        $text = $fname;
                    } elseif ($fname == '' && $lname != '') {
                        $text = $lname;
                    }   
                    return $text;
                    // return $row->first_name_parent ?? ''. ' ' . $row->last_name_parent ?? '';
                })
                ->editColumn('telp_parent', function ($row) {
                    $text = '';
                    $telp = $row->telp_parent ?? '';
                    $email = $row->email_parent ?? '';
                    if ($telp != '' && $email != '') {
                        $text = $telp . ' / ' . $email;
                    } elseif ($telp != '' && $email == '') {
                        $text = $telp;
                    } elseif ($telp == '' && $email != '') {
                        $text = $email;
                    }
                    return $text;
                    // return $row->telp_parent ?? '' . ' ' . $row->email_parent ?? '';
                })
                ->editColumn('first_name_student', function ($row) {
                    $text = '';
                    $fname = $row->first_name_student ?? '';
                    $lname = $row->last_name_student ?? '';
                    $nick = $row->nick_name_student ?? '';
                    if ($fname != '' && $lname != '' && $nick != '') {
                        $text = $fname . ' ' . $lname . ' (' . $nick . ')';
                    } elseif ($fname != '' && $lname == '' && $nick != '') {
                        $text = $fname . ' (' . $nick . ')';
                    } elseif ($fname == '' && $lname != '' && $nick != '') {
                        $text = $lname . ' (' . $nick . ')';
                    } elseif ($fname != '' && $lname != '' && $nick == '') {
                        $text = $fname . ' ' . $lname;
                    } elseif ($fname != '' && $lname == '' && $nick == '') {
                        $text = $fname;
                    } elseif ($fname == '' && $lname != '' && $nick == '') {
                        $text =  $lname;
                    }
                    return $text;
                    // return $row->first_name_student ?? '' . ' ' . $row->last_name_student ?? '' . ' (' . $row->nick_name_student ?? '' . ')';
                })
                ->editColumn('category', function ($row) {
                    if ($row->category) {
                        # code...
                        $text = 'category ' . $row->category;
                    }else {
                        $text = '';
                    }
                    return $text;
                })
                ->editColumn('address', function ($row) {
                    return $row->address ?? '';
                })
                
                ->addColumn('action', function ($row) {
                    $html = '';
                   
                    return $html;
                })->rawColumns(['checkbox', 'action'])->toJson();
        }
        return view('eiform.backend.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
