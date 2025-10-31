<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;

class QuestionBulkController extends Controller
{
    public function bulkUpdate(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมา
        $validated = $request->validate([
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'integer|exists:questions,id',
            'level' => 'required',
            'term' => 'required',
            'section' => 'required',
            // meta ปัจจุบันของแบบทดสอบ (ส่งมาเพื่ออ้างอิง)
            'quiz_id' => 'nullable|integer',
            'quiz_title' => 'nullable|string',
            'quiz_level' => 'nullable',
            'quiz_term' => 'nullable',
            'quiz_section' => 'nullable',
        ]);

        $ids = $validated['question_ids'];
        $updates = [
            'level' => $validated['level'],
            'term' => $validated['term'],
            'section' => $validated['section'],
        ];

        // อัปเดตฟิลด์ของคำถามที่เลือก
        Question::whereIn('id', $ids)->update($updates);

        // Quiz ต้นทาง (ก่อนย้าย) และปลายทาง (หลังย้าย)
        $startQuiz = isset($validated['quiz_id']) ? Quiz::find($validated['quiz_id']) : null;
        $endQuiz = Quiz::where('level', $updates['level'])
            ->where('term', $updates['term'])
            ->where('section', $updates['section'])
            ->first();

        // อัปเดตความสัมพันธ์ในตาราง pivot question_quiz
        // กรณีต้นทางและปลายทางเป็น quiz เดียวกัน ให้ทำครั้งเดียว
        if ($startQuiz && $endQuiz && $startQuiz->id === $endQuiz->id) {
            $endQuiz->questions()->syncWithoutDetaching($ids);
        } else {
            if ($startQuiz) {
                $startQuiz->questions()->detach($ids);
            }
            if ($endQuiz) {
                $endQuiz->questions()->syncWithoutDetaching($ids);
            }
        }

        $quizMeta = [
            'quiz_id' => $validated['quiz_id'] ?? null,
            'quiz_title' => $validated['quiz_title'] ?? null,
            'quiz_level' => $validated['quiz_level'] ?? null,
            'quiz_term' => $validated['quiz_term'] ?? null,
            'quiz_section' => $validated['quiz_section'] ?? null,
            'end_quiz_id' => $endQuiz?->id,
        ];

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตสำเร็จ',
            'updated_count' => count($ids),
            'quiz' => $quizMeta,
        ]);
    }
}
