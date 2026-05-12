<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LineCrmLead;
use Illuminate\Http\Request;

class N8nLineCrmController extends Controller
{
    public function handleLead(Request $request)
    {
        $data = $request->validate([
            'line_user_id' => 'required|string',
            'text' => 'nullable|string',
            'source' => 'nullable|string',
        ]);

        $text = trim($data['text'] ?? '');

        $lead = LineCrmLead::firstOrCreate(
            ['line_user_id' => $data['line_user_id']],
            [
                'source' => $data['source'] ?? 'line_oa',
                'step' => 'ask_level',
                'status' => 'new_lead',
                'lead_score' => 0,
            ]
        );

        $replyText = 'สวัสดีครับ 😊 รบกวนพิมพ์ระดับชั้น เช่น ม.1 ม.2 ม.3 ม.4 ม.5 ม.6 หรือ TCAS ครับ';

        if ($lead->step === 'ask_level') {

            $level = $this->detectLevel($text);

            if ($level) {

                $lead->level = $level;
                $lead->step = 'ask_interest';
                $lead->status = 'warm_lead';
                $lead->lead_score += 10; // Add score for answering level
                $lead->last_step_at = now();

                $replyText = "ตอนนี้อยู่ {$level} ใช่ไหมครับ 😊 สนใจเพิ่มเกรด สอบเข้า ปรับพื้นฐาน หรือทดลองเรียนครับ";
            }

        } elseif ($lead->step === 'ask_interest') {
            
            $interest = $this->detectInterest($text);
            
            $lead->interest = $interest ?? $text;
            $lead->step = 'waiting_admin'; // Admin handoff state
            $lead->status = 'hot_lead';
            $lead->lead_score += 10; // Add score for answering interest
            
            if ($lead->interest === 'TCAS') {
                $lead->lead_score += 20; // Bonus for TCAS
            }

            $lead->last_step_at = now();

            $replyText = "ขอบคุณครับ 😊 ตอนนี้ระบบได้รับข้อมูลของคุณเรียบร้อยแล้ว\nเดี๋ยวทีมงานจะแนะนำคอร์สที่เหมาะกับ {$lead->level} ให้นะครับ\n\n"
                        ."หรือหากต้องการเริ่มใหม่ พิมพ์: แก้ไข";

        } elseif ($lead->step === 'waiting_admin' || $lead->step === 'completed') {

            if (
                str_contains($text, 'แก้ไข') ||
                str_contains($text, 'เริ่มใหม่') ||
                str_contains($text, 'reset')
            ) {

                $lead->level = null;
                $lead->interest = null;

                $lead->step = 'ask_level';
                $lead->status = 'reset_lead';
                $lead->last_step_at = now();

                $replyText = "ได้ครับ 😊 รบกวนพิมพ์ระดับชั้นใหม่ เช่น ม.1 ม.2 ม.3 ม.4 ม.5 ม.6 หรือ TCAS ครับ";

            } else {

                $lead->lead_score += 5; // active ต่อเนื่อง
                $replyText = "ขอบคุณครับ 😊 ตอนนี้ระบบได้รับข้อมูลของคุณเรียบร้อยแล้ว\n\nหากต้องการแก้ไขข้อมูล พิมพ์: แก้ไข\nหรือรอทีมงานติดต่อกลับได้เลยครับ";
            }
        }

        $lead->last_message = $text;
        $lead->last_interaction_at = now();

        $lead->save();

        return response()->json([
            'success' => true,
            'lead' => $lead,
            'reply_text' => $replyText,
        ]);
    }

    private function detectLevel(string $text): ?string
    {
        $text = mb_strtolower($text);
        
        // Normalize text
        $text = str_replace(
            ['หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก'],
            ['1', '2', '3', '4', '5', '6'],
            $text
        );

        if (preg_match('/ม\.?1|m\.?1/i', $text)) return 'ม.1';
        if (preg_match('/ม\.?2|m\.?2/i', $text)) return 'ม.2';
        if (preg_match('/ม\.?3|m\.?3/i', $text)) return 'ม.3';
        if (preg_match('/ม\.?4|m\.?4/i', $text)) return 'ม.4';
        if (preg_match('/ม\.?5|m\.?5/i', $text)) return 'ม.5';
        if (preg_match('/ม\.?6|m\.?6/i', $text)) return 'ม.6';
        if (str_contains($text, 'tcas')) return 'TCAS';

        return null;
    }

    private function detectInterest(string $text): ?string
    {
        $text = mb_strtolower($text);
        
        if (str_contains($text, 'เพิ่มเกรด')) return 'เพิ่มเกรด';
        if (str_contains($text, 'สอบเข้า')) return 'สอบเข้า';
        if (str_contains($text, 'ปรับพื้นฐาน')) return 'ปรับพื้นฐาน';
        if (str_contains($text, 'tcas')) return 'TCAS';
        if (str_contains($text, 'ทดลองเรียน')) return 'ทดลองเรียน';
        
        return null;
    }

    public function followUpLeads()
    {
        $leads = LineCrmLead::query()
            // เลือกเฉพาะ lead ที่ต้อง follow-up
            ->whereIn('status', [
                'hot_lead',
                'waiting_admin',
            ])
            // ต้องมี LINE user id
            ->whereNotNull('line_user_id')
            // ลูกค้าหายเกิน 1 ชั่วโมง
            ->where('last_interaction_at', '<=', now()->subHours(1))
            // ยังไม่ถูก follow-up วันนี้
            ->where(function ($query) {
                $query->whereNull('last_follow_up_at')
                      ->orWhere('last_follow_up_at', '<=', now()->subDay());
            })
            // จำกัดจำนวนต่อรอบ
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $leads->count(),
            'leads' => $leads,
        ]);
    }
}
