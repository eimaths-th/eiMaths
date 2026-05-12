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
            ]
        );

        $replyText = 'สวัสดีครับ 😊 รบกวนพิมพ์ระดับชั้น เช่น ม.1 ม.2 ม.3 ม.4 ม.5 ม.6 หรือ TCAS ครับ';

        if ($lead->step === 'ask_level') {

            $level = $this->detectLevel($text);

            if ($level) {

                $lead->level = $level;
                $lead->step = 'ask_interest';
                $lead->status = 'warm_lead';

                $replyText = "ตอนนี้อยู่ {$level} ใช่ไหมครับ 😊 สนใจเพิ่มเกรด หรือสอบเข้าครับ";
            }

        } elseif ($lead->step === 'ask_interest') {

            $lead->interest = $text;
            $lead->step = 'completed';
            $lead->status = 'hot_lead';

            $replyText = "ขอบคุณครับ 😊 เดี๋ยวทีมงานจะแนะนำคอร์สที่เหมาะกับ {$lead->level} ให้ครับ";
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

        if (str_contains($text, 'ม.1') || str_contains($text, 'ม1')) return 'ม.1';
        if (str_contains($text, 'ม.2') || str_contains($text, 'ม2')) return 'ม.2';
        if (str_contains($text, 'ม.3') || str_contains($text, 'ม3')) return 'ม.3';
        if (str_contains($text, 'ม.4') || str_contains($text, 'ม4')) return 'ม.4';
        if (str_contains($text, 'ม.5') || str_contains($text, 'ม5')) return 'ม.5';
        if (str_contains($text, 'ม.6') || str_contains($text, 'ม6')) return 'ม.6';
        if (str_contains($text, 'tcas')) return 'TCAS';

        return null;
    }
}
