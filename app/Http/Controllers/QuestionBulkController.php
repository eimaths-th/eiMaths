<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

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
        // update position ของ id quiz ที่ปลายทาง ให้รันต่อท้ายสุดต่อจากลำดับเดิม
        if ($endQuiz && !empty($ids)) {
            $destQuizId = $endQuiz->id;
            $maxPos = DB::table('question_quiz')
                ->where('quiz_id', $destQuizId)
                ->max('position'); // อาจเป็น null ถ้ายังไม่เคยกำหนด

            $pos = (int)($maxPos ?? 0);
            foreach ($ids as $qid) {
                DB::table('question_quiz')
                    ->where('quiz_id', $destQuizId)
                    ->where('question_id', $qid)
                    ->update(['position' => ++$pos]);
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

    public function updateOrder(Request $request, int $quizId)
    {
        $validated = $request->validate([
            'ordered_ids' => 'required|array|min:1',
            'ordered_ids.*' => 'integer|exists:questions,id',
        ]);

        $orderedIds = $validated['ordered_ids'];

        $quiz = Quiz::findOrFail($quizId);

        // Ensure all IDs belong to this quiz and determine a deterministic base order
        $hasAnyPosition = DB::table('question_quiz')
            ->where('quiz_id', $quiz->id)
            ->whereNotNull('position')
            ->exists();

        if ($hasAnyPosition) {
            // Use existing position order
            $existingIds = DB::table('question_quiz')
                ->where('quiz_id', $quiz->id)
                ->orderBy('position', 'asc')
                ->pluck('question_id')
                ->toArray();
        } else {
            // First time: no positions yet — build directly from current UI order (ordered_ids)
            // Get all question ids of this quiz to validate subset/superset
            $existingIds = DB::table('question_quiz')
                ->where('quiz_id', $quiz->id)
                ->pluck('question_id')
                ->toArray();
        }
        $diff = array_diff($orderedIds, $existingIds);
        if (!empty($diff)) {
            return response()->json([
                'success' => false,
                'message' => 'มีคำถามที่ไม่อยู่ในแบบทดสอบนี้',
                'invalid_ids' => array_values($diff)
            ], 422);
        }

        // Build position mapping (1-based) — put orderedIds first, then remaining in base order
        $position = 1;
        foreach ($orderedIds as $qid) {
            DB::table('question_quiz')
                ->where('quiz_id', $quiz->id)
                ->where('question_id', $qid)
                ->update(['position' => $position++]);
        }

        $remaining = array_values(array_diff($existingIds, $orderedIds));
        // If there are remaining ids (e.g., user re-ordered only a subset), append them by question_id asc for determinism
        if (!empty($remaining)) {
            $remaining = DB::table('question_quiz')
                ->where('quiz_id', $quiz->id)
                ->whereIn('question_id', $remaining)
                ->orderBy('question_id', 'asc')
                ->pluck('question_id')
                ->toArray();
        }
        foreach ($remaining as $qid) {
            DB::table('question_quiz')
                ->where('quiz_id', $quiz->id)
                ->where('question_id', $qid)
                ->update(['position' => $position++]);
        }

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตลำดับคำถามสำเร็จ',
        ]);
    }
}
