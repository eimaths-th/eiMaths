<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AffiliateAuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showRegistrationFrom()
    {
        //
        $page = [
            "id" => 1,
            "title" => json_decode('{"en":"ยินดีต้อนรับสู่ระบบจัดการการเรียนรู้ eiMaths","th":"Welcome to Infix Learning Management System"}', true),
            "banner" => "",
            "slogans1" => json_decode('{"en":"ความเป็นเลิศ.","th":"Excellence."}', true),
            "slogans2" => json_decode('{"en":"ชุมชน.","th":"Community."}', true),
            "slogans3" => json_decode('{"en":"ความหลากหลาย.","th":"Diversity."}', true),
            "created_at" => "2024-08-08 14:56:32",
            "updated_at" => "2024-09-16 10:47:34",
            "reg_title" => json_decode('{"en":"ยินดีต้อนรับสู่ระบบจัดการการเรียนรู้ eiMaths","th":"Welcome to Infix Learning Management System"}', true),
            "reg_banner" => "",
            "reg_slogans1" => json_decode('{"en":"ความเป็นเลิศ.","th":"Excellence"}', true),
            "reg_slogans2" => json_decode('{"en":"ชุมชน.","th":"Community"}', true),
            "reg_slogans3" => json_decode('{"en":"ความหลากหลาย.","th":"Excellence"}', true),
            "forget_title" => json_decode('{"en":"ยินดีต้อนรับสู่ระบบจัดการการเรียนรู้ eiMaths","th":"Welcome to Infix Learning Management System"}', true),
            "forget_banner" => "",
            "forget_slogans1" => json_decode('{"en":"ความเป็นเลิศ.","th":"Excellence"}', true),
            "forget_slogans2" => json_decode('{"en":"ชุมชน.","th":"Community"}', true),
            "forget_slogans3" => json_decode('{"en":"ความหลากหลาย.","th":"Excellence"}', true),
            "lms_id" => 1
        ];
        $custom_field = [
            "id" => 1,
            "show_company" => 0,
            "show_gender" => 0,
            "show_student_type" => 0,
            "show_identification_number" => 0,
            "show_job_title" => 0,
            "show_dob" => 0,
            "show_name" => 1,
            "required_company" => 0,
            "required_gender" => 0,
            "required_student_type" => 0,
            "required_identification_number" => 0,
            "required_job_title" => 0,
            "required_dob" => 0,
            "required_name" => 1,
            "editable_company" => 1,
            "editable_gender" => 1,
            "editable_student_type" => 1,
            "editable_identification_number" => 1,
            "editable_job_title" => 1,
            "editable_dob" => 1,
            "editable_name" => 1,
            "created_at" => "2024-08-08 14:56:34",
            "updated_at" => "2024-08-08 14:56:34",
            "show_phone" => 1,
            "required_phone" => 0,
            "editable_phone" => 1,
            "lms_id" => 1,
            "show_institute" => 0,
            "required_institute" => 0,
            "editable_institute" => 1
        ];
        return view('affiliate.auth.registration', compact('page', 'custom_field'));
    }
    public function index()
    {
        //
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
