<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ParentRegistrationController extends Controller
{
    public function requestCode(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'telp' => 'required|string|max:32', 'type' => 'required|in:father,mother']);
        $ids = Contact::where($data['type'].'_email', $data['email'])->where($data['type'].'_mobile', $data['telp'])->pluck('id')->all();
        $id = (string) Str::uuid();
        // Equal outward response whether or not a family matches the registration.
        if ($ids && !Parents::where('email', $data['email'])->exists()) {
            $code = (string) random_int(100000, 999999);
            DB::table('parent_registration_challenges')->upsert([[
                'id' => $id, 'email' => $data['email'], 'phone' => $data['telp'], 'relationship' => $data['type'],
                'student_ids' => json_encode($ids), 'code_hash' => Hash::make($code), 'attempts' => 0,
                'expires_at' => now()->addMinutes(10), 'consumed_at' => null, 'created_at' => now(), 'updated_at' => now(),
            ]], ['email'], ['id', 'phone', 'relationship', 'student_ids', 'code_hash', 'attempts', 'expires_at', 'consumed_at', 'updated_at']);
            Mail::raw('รหัสยืนยันลงทะเบียน eiMaths: '.$code.' รหัสนี้ใช้ได้ภายใน 10 นาที', fn ($mail) => $mail->to($data['email'])->subject('ยืนยันบัญชีผู้ปกครอง eiMaths'));
        }
        return response()->json(['success' => true, 'verification_id' => $id, 'message' => 'หากข้อมูลตรงกับทะเบียน ระบบจะส่งรหัสยืนยันไปยังอีเมลของท่าน']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'verification_id' => 'required|uuid', 'code' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);
        // Do not throw inside this transaction on invalid codes: failed-attempt counters must commit.
        $result = DB::transaction(function () use ($data) {
            $challenge = DB::table('parent_registration_challenges')->where('id', $data['verification_id'])->lockForUpdate()->first();
            if (!$challenge || $challenge->consumed_at || $challenge->attempts >= 5 || now()->gte($challenge->expires_at)) {
                return null;
            }
            DB::table('parent_registration_challenges')->where('id', $challenge->id)->increment('attempts');
            if (!Hash::check($data['code'], $challenge->code_hash)) {
                return null;
            }
            DB::table('parent_registration_challenges')->where('id', $challenge->id)->update(['consumed_at' => now()]);
            $students = Contact::whereIn('id', json_decode($challenge->student_ids, true))
                ->where($challenge->relationship.'_email', $challenge->email)
                ->where($challenge->relationship.'_mobile', $challenge->phone)->get();
            if ($students->isEmpty() || Parents::where('email', $challenge->email)->exists()) {
                return null;
            }
            $name = trim((string) $students->first()->getAttribute($challenge->relationship.'_name'));
            [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');
            $parent = Parents::create([
                'student_id' => $students->pluck('id')->implode(','), 'centre_id' => $students->first()->centre,
                'fname' => $first, 'lname' => $last, 'email' => $challenge->email,
                'telp' => $challenge->phone, 'relationship' => $challenge->relationship,
                'password' => Hash::make($data['password']),
            ]);
            return $parent;
        });
        abort_unless($result, 422, 'รหัสยืนยันไม่ถูกต้อง หมดอายุ หรือบัญชีนี้ลงทะเบียนแล้ว');
        return response()->json(['success' => true, 'parent_id' => $result->id, 'message' => 'ลงทะเบียนสำเร็จ กรุณาเข้าสู่ระบบ']);
    }
}
