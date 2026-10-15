<?php

namespace App\Services;

use App\Models\bookuse;
use App\Models\Contact;
use App\Models\Histrories;
use App\Models\level;
use App\Models\Order;
use App\Models\OrderDetailList;
use App\Models\term;
use App\Models\User;
use App\Support\Branch\TeacherRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FastHistoryService
{
    public function BuildPreview(Contact $student, ?User $actor = null, ?Carbon $now = null): array
    {
        $now = ($now ?? Carbon::now())->copy();
        $courses = $this->BuildCourseStates($student);
        $course = $this->ResolveLatestIncompleteCourse($courses);
        $histories = $course['histories'];
        $first_history = $histories->first();
        $start_date = $first_history?->start_date ?: $now->toDateString();
        $end_date = $first_history?->end_date ?: Carbon::parse($start_date)->addMonths(4)->toDateString();
        $teacher_id = $this->ResolveTeacherId($histories, $student, $actor);

        return [
            'student_id' => (int) $student->id,
            'student_code' => $student->code,
            'student_name' => $student->name,
            'level_id' => (int) $course['level']->id,
            'level_name' => $course['level']->name,
            'term' => (string) $course['term_name'],
            'bookuse_id' => (int) $course['bookuse']->id,
            'bookuse_name' => $course['bookuse']->name,
            'course_quantity' => $course['course_quantity'],
            'attended_count' => $course['attended_count'],
            'remaining_after' => max($course['course_quantity'] - $course['attended_count'] - 1, 0),
            'date' => $now->toDateString(),
            'stime' => $now->format('H:i:s'),
            'etime' => $now->copy()->addHours(2)->format('H:i:s'),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'teacher_id' => $teacher_id,
            'registered_courses' => $this->FormatRegisteredCourses($courses),
            'teacher_options' => $this->BuildTeacherOptions($student, $teacher_id),
        ];
    }

    public function BuildRenewalStatus(Contact $student, ?Carbon $now = null): array
    {
        $now = ($now ?? Carbon::now())->copy();

        try {
            $courses = $this->BuildCourseStates($student);
        } catch (FastHistoryException) {
            return [
                'status' => 'unknown',
                'reason' => 'course_data_unavailable',
                'paid_total' => 0,
                'free_course' => max((int) $student->free_course, 0),
                'total' => 0,
                'attended_count' => 0,
                'remaining' => null,
                'expires_on' => null,
            ];
        }

        $paid_total = array_sum(array_column($courses, 'course_quantity'));
        $attended_count = array_sum(array_column($courses, 'attended_count'));
        $free_course = max((int) $student->free_course, 0);
        $total = $paid_total + $free_course;
        $remaining = max($total - $attended_count, 0);
        $current_course = $this->ResolveRenewalCourse($courses);
        $expires_on = $this->ResolveCourseExpiry($current_course);
        $is_expired = $expires_on !== null
            && $now->copy()->startOfDay()->greaterThan(Carbon::parse($expires_on)->startOfDay());
        $reason = $remaining === 0
            ? 'course_exhausted'
            : ($is_expired ? 'course_expired' : null);

        return [
            'status' => $reason === null ? 'active' : 'pending',
            'reason' => $reason,
            'paid_total' => $paid_total,
            'free_course' => $free_course,
            'total' => $total,
            'attended_count' => $attended_count,
            'remaining' => $remaining,
            'expires_on' => $expires_on,
        ];
    }

    public function CreateHistory(
        int $student_id,
        ?User $actor = null,
        ?Carbon $now = null,
        array $overrides = []
    ): Histrories {
        $now = ($now ?? Carbon::now())->copy();

        return DB::transaction(function () use ($student_id, $actor, $overrides, $now): Histrories {
            $student = Contact::query()->lockForUpdate()->find($student_id);
            if (! $student) {
                throw new FastHistoryException(__('students.check_in.student_not_found'), 404);
            }

            $preview = $this->BuildPreview($student, $actor, $now);
            $selection = $this->ResolveSelection($student, $preview, $overrides);
            $already_checked_in = Histrories::query()
                ->where('student_id', $student->id)
                ->where('level_id', $selection['level_id'])
                ->where('term', $selection['term'])
                ->whereDate('date', $selection['date'])
                ->exists();

            if ($already_checked_in) {
                throw new FastHistoryException(__('students.check_in.duplicate'));
            }

            return Histrories::create([
                'centre' => $student->centre,
                'teacher_id' => $selection['teacher_id'],
                'student_id' => $student->id,
                'level_id' => $selection['level_id'],
                'level_name' => $selection['level_name'],
                'term' => $selection['term'],
                'bookuse' => (string) $selection['bookuse_id'],
                'course_remaining' => (string) $selection['remaining_after'],
                'date' => $selection['date'],
                'stime' => $selection['stime'],
                'etime' => $selection['etime'],
                'start_date' => $selection['start_date'],
                'end_date' => $selection['end_date'],
                'comment' => '',
                'signature' => '',
            ]);
        });
    }

    private function ResolveLatestIncompleteCourse(array $courses): array
    {
        $latest_attempted_course = null;
        foreach ($courses as $course) {
            if ($course['attended_count'] > 0 && $course['attended_count'] < $course['course_quantity']) {
                $latest_attempted_course = $course;
            }
        }
        if ($latest_attempted_course !== null) {
            return $latest_attempted_course;
        }

        foreach ($courses as $course) {
            if ($course['attended_count'] < $course['course_quantity']) {
                return $course;
            }
        }

        throw new FastHistoryException(__('students.check_in.completed_all_courses'));
    }

    private function ResolveRenewalCourse(array $courses): ?array
    {
        $latest_attempted_course = null;
        foreach ($courses as $course) {
            if ($course['attended_count'] > 0 && $course['attended_count'] < $course['course_quantity']) {
                $latest_attempted_course = $course;
            }
        }
        if ($latest_attempted_course !== null) {
            return $latest_attempted_course;
        }

        foreach ($courses as $course) {
            if ($course['attended_count'] < $course['course_quantity']) {
                return $course;
            }
        }

        // Extra lessons still belong to the last paid course and retain its expiry.
        // Completed courses with no extra lessons are already classified as exhausted.
        return $courses === [] ? null : $courses[array_key_last($courses)];
    }

    private function ResolveCourseExpiry(?array $course): ?string
    {
        if ($course === null) {
            return null;
        }

        $first_history = $course['histories']->first() ?? null;
        $end_date = $first_history?->end_date;
        if (! $end_date) {
            return null;
        }

        try {
            return Carbon::parse($end_date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function ResolveSelection(Contact $student, array $preview, array $overrides): array
    {
        $level_id = $this->ParsePositiveInteger(
            $this->ResolveOverride($overrides, 'level_id', $preview['level_id']),
            __('students.check_in.valid_level')
        );
        $term = $this->ParseTerm(
            $this->ResolveOverride($overrides, 'term', $preview['term'])
        );
        $bookuse_id = $this->ParsePositiveInteger(
            $this->ResolveOverride($overrides, 'bookuse', $preview['bookuse_id']),
            __('students.check_in.valid_book')
        );

        $selected_course = collect($preview['registered_courses'])->first(
            fn (array $course): bool => (int) $course['level_id'] === $level_id
                && (string) $course['term'] === (string) $term
        );
        if (! $selected_course) {
            throw new FastHistoryException(__('students.check_in.invalid_course'));
        }

        $selected_book = collect($selected_course['bookuses'])->first(
            fn (array $book): bool => (int) $book['id'] === $bookuse_id
        );
        if (! $selected_book) {
            throw new FastHistoryException(__('students.check_in.invalid_book'));
        }

        $course_quantity = (int) $selected_book['quantity'];
        $attended_count = (int) $selected_course['attended_count'];
        if ($course_quantity < 1 || $attended_count >= $course_quantity) {
            throw new FastHistoryException(__('students.check_in.no_remaining'));
        }

        $teacher_id = $this->ParseNonNegativeInteger(
            $this->ResolveOverride($overrides, 'teacher_id', $preview['teacher_id']),
            __('students.check_in.valid_teacher')
        );
        $this->ValidateTeacher($teacher_id, $student);

        $date = $this->ParseDate(
            $this->ResolveOverride($overrides, 'date', $preview['date'])
        );
        $stime = $this->ParseTime(
            $this->ResolveOverride($overrides, 'stime', $preview['stime']),
            __('students.check_in.valid_start_time')
        );
        $etime = $this->ParseTime(
            $this->ResolveOverride($overrides, 'etime', $preview['etime']),
            __('students.check_in.valid_end_time')
        );
        if ($this->TimeToSeconds($etime) <= $this->TimeToSeconds($stime)) {
            throw new FastHistoryException(__('students.check_in.end_before_start'));
        }

        $start_date = $selected_course['start_date'] ?: $date->toDateString();
        $end_date = $selected_course['end_date']
            ?: $date->copy()->addMonths(4)->toDateString();

        return [
            'level_id' => $level_id,
            'level_name' => (string) $selected_course['level_name'],
            'term' => (string) $term,
            'bookuse_id' => $bookuse_id,
            'teacher_id' => $teacher_id,
            'remaining_after' => max($course_quantity - $attended_count - 1, 0),
            'date' => $date->toDateString(),
            'stime' => $stime,
            'etime' => $etime,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
    }

    private function ResolveOverride(array $overrides, string $key, mixed $default): string
    {
        if (! array_key_exists($key, $overrides)) {
            return (string) $default;
        }

        if (! is_scalar($overrides[$key]) || trim((string) $overrides[$key]) === '') {
            throw new FastHistoryException(__('students.check_in.missing_details'));
        }

        return trim((string) $overrides[$key]);
    }

    private function ParsePositiveInteger(string $value, string $message): int
    {
        if (! preg_match('/^[1-9][0-9]*$/', $value)) {
            throw new FastHistoryException($message);
        }

        return (int) $value;
    }

    private function ParseNonNegativeInteger(string $value, string $message): int
    {
        if (! preg_match('/^(0|[1-9][0-9]*)$/', $value)) {
            throw new FastHistoryException($message);
        }

        return (int) $value;
    }

    private function ParseTerm(string $value): int
    {
        $term = $this->ParsePositiveInteger($value, __('students.check_in.valid_term'));
        if ($term > 4) {
            throw new FastHistoryException(__('students.check_in.valid_term'));
        }

        return $term;
    }

    private function ParseDate(string $value): Carbon
    {
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            $date = false;
        }

        if (! $date || $date->format('Y-m-d') !== $value) {
            throw new FastHistoryException(__('students.check_in.invalid_date'));
        }

        return $date;
    }

    private function ParseTime(string $value, string $message): string
    {
        try {
            $time = Carbon::createFromFormat('!H:i:s', $value);
        } catch (\Throwable) {
            $time = false;
        }

        if (! $time || $time->format('H:i:s') !== $value) {
            throw new FastHistoryException($message);
        }

        return $time->format('H:i:s');
    }

    private function TimeToSeconds(string $time): int
    {
        [$hour, $minute, $second] = array_map('intval', explode(':', $time));

        return ($hour * 3600) + ($minute * 60) + $second;
    }

    private function ValidateTeacher(int $teacher_id, Contact $student): void
    {
        if ($teacher_id === 0) {
            return;
        }

        $teacher_exists = User::query()
            ->whereKey($teacher_id)
            ->where('department_id', $student->centre)
            ->exists();
        if (! $teacher_exists) {
            throw new FastHistoryException(__('students.check_in.invalid_teacher'));
        }
    }

    private function FormatRegisteredCourses(array $courses): array
    {
        return array_map(function (array $course): array {
            $first_history = $course['histories']->first();

            return [
                'level_id' => (int) $course['level']->id,
                'level_name' => (string) $course['level']->name,
                'term' => (string) $course['term_name'],
                'bookuse_id' => (int) $course['bookuse']->id,
                'bookuse_name' => (string) $course['bookuse']->name,
                'course_quantity' => (int) $course['course_quantity'],
                'attended_count' => (int) $course['attended_count'],
                'remaining_after' => max((int) $course['course_quantity'] - (int) $course['attended_count'] - 1, 0),
                'start_date' => $first_history?->start_date,
                'end_date' => $first_history?->end_date,
                'bookuses' => $course['bookuses']->map(fn (bookuse $book): array => [
                    'id' => (int) $book->id,
                    'name' => (string) $book->name,
                    'quantity' => (int) $book->qty,
                ])->values()->all(),
            ];
        }, $courses);
    }

    private function BuildTeacherOptions(Contact $student, int $selected_teacher_id): array
    {
        $teachers = User::query()
            ->where('department_id', $student->centre)
            ->where('disable', 0)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', TeacherRole::teacherRoleNames()))
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($selected_teacher_id > 0 && ! $teachers->contains('id', $selected_teacher_id)) {
            $selected_teacher = User::query()
                ->whereKey($selected_teacher_id)
                ->where('department_id', $student->centre)
                ->first(['id', 'name']);
            if ($selected_teacher) {
                $teachers->push($selected_teacher);
            }
        }

        return $teachers
            ->sortBy(fn (User $teacher) => mb_strtolower((string) $teacher->name))
            ->values()
            ->map(fn (User $teacher): array => [
                'id' => (int) $teacher->id,
                'name' => (string) $teacher->name,
            ])
            ->all();
    }

    private function BuildCourseStates(Contact $student): array
    {
        $start_level_id = (int) $student->level;
        $end_level_id = (int) ($student->level2 ?: $student->level);
        if ($start_level_id < 1 || $end_level_id < $start_level_id) {
            throw new FastHistoryException(__('students.check_in.no_valid_course'));
        }

        $start_term_value = $student->term ?: $student->term_name;
        $end_term_value = $student->term2 ?: ($student->term2_name ?: $start_term_value);
        $start_term_number = $this->ResolveTermNumber($start_level_id, $start_term_value, 1);
        $end_term_number = $this->ResolveTermNumber($end_level_id, $end_term_value, 4);
        $levels = level::query()
            ->whereBetween('id', [$start_level_id, $end_level_id])
            ->orderBy('id')
            ->get()
            ->keyBy('id');
        $order_ids = Order::query()->where('cid', $student->id)->pluck('id');
        $order_details = $order_ids->isEmpty()
            ? new Collection
            : OrderDetailList::query()->whereIn('order_id', $order_ids)->orderBy('id')->get();
        $courses = [];

        for ($level_id = $start_level_id; $level_id <= $end_level_id; $level_id++) {
            $level_record = $levels->get($level_id);
            if (! $level_record) {
                throw new FastHistoryException(__('students.check_in.invalid_registered_level'));
            }

            $first_term = $level_id === $start_level_id ? $start_term_number : 1;
            $last_term = $level_id === $end_level_id ? $end_term_number : 4;
            for ($term_number = $first_term; $term_number <= $last_term; $term_number++) {
                $book = $this->ResolveBookuse(
                    $student,
                    $level_record,
                    $term_number,
                    $start_level_id,
                    $start_term_number,
                    $end_level_id,
                    $end_term_number,
                    $order_details
                );
                if (! $book || (int) $book->qty < 1) {
                    throw new FastHistoryException(__('students.check_in.no_valid_book'));
                }

                $term_name = (string) $term_number;
                $histories = Histrories::query()
                    ->where('student_id', $student->id)
                    ->where('level_id', $level_id)
                    ->where('term', $term_name)
                    ->orderBy('date')
                    ->orderBy('id')
                    ->get();
                $bookuses = $this->BuildBookuseOptions($level_record, $term_number, $book);

                $courses[] = [
                    'level' => $level_record,
                    'term_name' => $term_name,
                    'bookuse' => $book,
                    'course_quantity' => (int) $book->qty,
                    'attended_count' => $histories->count(),
                    'histories' => $histories,
                    'bookuses' => $bookuses,
                ];
            }
        }

        if ($courses === []) {
            throw new FastHistoryException(__('students.check_in.no_registered_course'));
        }

        return $courses;
    }

    private function BuildBookuseOptions(level $level_record, int $term_number, bookuse $selected_book): Collection
    {
        $bookuses = bookuse::query()
            ->where('level_id', $level_record->id)
            ->where('term_id', $term_number)
            ->where('qty', '>', 0)
            ->where('status', 1)
            ->orderByDesc('type')
            ->orderBy('id')
            ->get();

        if (! $bookuses->contains('id', $selected_book->id)) {
            $bookuses->push($selected_book);
        }

        return $bookuses->unique('id')->values();
    }

    private function ResolveBookuse(
        Contact $student,
        level $level_record,
        int $term_number,
        int $start_level_id,
        int $start_term_number,
        int $end_level_id,
        int $end_term_number,
        Collection $order_details
    ): ?bookuse {
        $endpoint_bookuse_id = null;
        if ($level_record->id === $start_level_id && $term_number === $start_term_number) {
            $endpoint_bookuse_id = $student->bookuse;
        } elseif ($level_record->id === $end_level_id && $term_number === $end_term_number) {
            $endpoint_bookuse_id = $student->bookuse2 ?: $student->bookuse;
        }

        if ($endpoint_bookuse_id) {
            $endpoint_bookuse = bookuse::query()
                ->whereKey($endpoint_bookuse_id)
                ->where('level_id', $level_record->id)
                ->where('term_id', $term_number)
                ->first();
            if ($endpoint_bookuse) {
                return $endpoint_bookuse;
            }
        }

        $term_record = term::query()
            ->where('level_id', $level_record->id)
            ->where('name', (string) $term_number)
            ->first();
        $term_name = (string) ($term_record?->name ?: $term_number);
        $order_detail = $order_details->reverse()->first(function (OrderDetailList $detail) use ($level_record, $term_name): bool {
            return mb_strtolower(trim((string) $detail->level)) === mb_strtolower(trim((string) $level_record->name))
                && trim((string) $detail->term) === trim($term_name);
        });

        if ($order_detail) {
            $book_from_order = bookuse::query()
                ->where('level_id', $level_record->id)
                ->where('term_id', $term_number)
                ->where('name', $order_detail->book)
                ->first();
            if ($book_from_order) {
                return $book_from_order;
            }
        }

        return bookuse::query()
            ->where('level_id', $level_record->id)
            ->where('term_id', $term_number)
            ->where('status', 1)
            ->orderByDesc('type')
            ->orderBy('id')
            ->first()
            ?: bookuse::query()
                ->where('level_id', $level_record->id)
                ->where('term_id', $term_number)
                ->orderByDesc('type')
                ->orderBy('id')
                ->first();
    }

    private function ResolveTermNumber(int $level_id, $term_value, int $default): int
    {
        $value = trim((string) $term_value);
        $term_record = term::query()
            ->where('level_id', $level_id)
            ->where('name', $value)
            ->first();
        if ($term_record && is_numeric($term_record->name)) {
            return max(1, min(4, (int) $term_record->name));
        }

        if (ctype_digit($value)) {
            $term_record = term::query()
                ->where('level_id', $level_id)
                ->whereKey((int) $value)
                ->first();
            if ($term_record && is_numeric($term_record->name)) {
                return max(1, min(4, (int) $term_record->name));
            }

            $numeric_value = (int) $value;
            if ($numeric_value >= 1 && $numeric_value <= 4) {
                return $numeric_value;
            }
        }

        return $default;
    }

    private function ResolveTeacherId(Collection $histories, Contact $student, ?User $actor): int
    {
        $last_history = $histories->sortByDesc(fn (Histrories $history) => [$history->date, $history->id])->first();
        $last_teacher_id = (int) ($last_history?->teacher_id ?? 0);
        if ($last_teacher_id > 0 && User::query()
            ->whereKey($last_teacher_id)
            ->where('department_id', $student->centre)
            ->exists()) {
            return $last_teacher_id;
        }

        if ($actor && (int) $actor->department_id === (int) $student->centre) {
            return (int) $actor->id;
        }

        return 0;
    }
}
