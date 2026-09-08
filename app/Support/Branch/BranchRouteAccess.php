<?php

namespace App\Support\Branch;

use App\Models\User;
use Illuminate\Http\Request;

final class BranchRouteAccess
{
    public function authorize(Request $request, User $actor): void
    {
        $action = $request->route()?->getActionName() ?? '';
        [$class, $method] = array_pad(explode('@', $action), 2, '');
        $controller = class_basename($class);
        $method = strtolower($method);
        $central = [
            'QrCodePaymentController' => 'financial-settings-manage',
            'LineApiController' => 'integration-settings-manage',
            'TelegramSettingController' => 'integration-settings-manage',
            'FooterSettingController' => 'central-settings-manage',
            'PromotionImageController' => 'central-settings-manage',
            'TermfeeController' => 'financial-settings-manage',
            'ParameterController' => 'central-settings-manage',
        ];
        if (isset($central[$controller])) {
            abort_unless(BranchAccess::isCentral($actor) && $actor->can($central[$controller]), 403);
            return;
        }
        if ($controller === 'FeedbackController' && $method === 'updatestatus') {
            abort_unless(BranchAccess::isCentral($actor) && $actor->can('support-manage'), 403);
        }
        if ($controller === 'BookShipmentController') {
            abort_unless(BranchAccess::canCrossBranches($actor), 403);
        }
        if ($controller === 'BookReceiptController' && !BranchAccess::isCentral($actor)) {
            abort_unless(BranchAccess::moduleEnabled((int) $actor->department_id, 'stock'), 403);
        }
        if ($controller === 'ResultController') {
            abort_unless($actor->can('quiz-list'), 403);
        }
        $prefixes = [
            'ContactController' => 'student', 'DiscontinueController' => 'student',
            'ParentController' => 'parent', 'HistoriesController' => 'history',
            'HistoryStudentImgController' => 'history', 'ProductController' => 'product',
            'OrderController' => 'order', 'ReceiptController' => 'receipt', 'InvoiceController' => 'invoice',
            'PositionController' => 'department', 'TeachingPeriodController' => 'teaching',
            'AssignQuizToStudentController' => 'quiz', 'CrmLeadController' => 'crm-leads',
            'TrackingLinkController' => 'crm-leads',
        ];
        if ($controller === 'ContactController' && in_array($method, ['studentslogin', 'studentschecklogin', 'studentslogout', 'quizuserlogout', 'thank_you'], true)) {
            return;
        }
        if ($controller === 'TrackingLinkController' && in_array($method, ['resolve', 'redirect'], true)) {
            return;
        }
        if ($controller === 'CourseController') {
            if (in_array($method, ['coursespendingstore', 'uploadslip', 'senmail'], true)) {
                return; // Public application-token checks are handled by that flow.
            }
            if (str_contains($method, 'pending') || $method === 'fetchslip' || $method === 'getcheckrefstudnet') {
                $prefixes[$controller] = 'course-pending';
            } else {
                abort_unless(BranchAccess::isCentral($actor) && $actor->can('course-manage'), 403);
                return;
            }
        }
        if (str_starts_with($class, 'App\\Http\\Livewire\\Admin\\Admin')) {
            abort(404); // Replaced by the single, guarded User Management flow.
        }
        if ($controller === 'DepartmentController' && $method !== 'find') {
            if (in_array($method, ['store', 'update', 'destroy', 'destroy_all'], true)) {
                abort_unless(BranchAccess::isCentral($actor), 403);
            }
        }
        if (!isset($prefixes[$controller])) {
            return; // Other modules retain their policies; scoped models still apply.
        }
        $prefix = $prefixes[$controller];
        $operation = $this->operation($request, $method);
        if ($controller === 'ContactController' && (str_contains($method, 'password') || str_contains($method, 'genneratestudent'))) {
            $operation = 'edit';
        }
        if ($controller === 'ProductController' && in_array($method, ['add_stock', 'rm_stock'], true)) {
            $operation = 'edit';
        }
        if (in_array($prefix, ['order', 'receipt', 'invoice', 'product'], true) && in_array($method, ['show', 'print', 'printproduct'], true)) {
            $operation = 'show';
        }
        $permission = $prefix.'-'.$operation;
        if (in_array($prefix, ['crm-leads', 'course-pending'], true) && $operation === 'list') {
            $permission = $prefix === 'crm-leads' ? 'crm-leads-menu-view' : 'course-pending-list';
        }
        abort_unless($actor->can($permission), 403, 'You do not have permission for this action.');
        $this->validateTargets($request, $controller);
    }

    private function operation(Request $request, string $method): string
    {
        if (str_contains($method, 'destroy') || str_contains($method, 'delete')) { return 'delete'; }
        if (in_array($method, ['store', 'create', 'save'], true) || str_ends_with($method, '_store') || str_ends_with($method, 'stdstore')) { return 'create'; }
        if (str_contains($method, 'export')) { return 'export'; }
        if (in_array($method, ['edit', 'update', 'confirm', 'cancle', 'generate', 'restart', 'restart_all'], true) || (!$request->isMethodSafe() && !str_starts_with($method, 'get') && !str_starts_with($method, 'search') && !str_starts_with($method, 'find'))) { return 'edit'; }
        return 'list';
    }

    private function validateTargets(Request $request, string $controller): void
    {
        $models = [
            'ContactController' => \App\Models\Contact::class, 'DiscontinueController' => \App\Models\Contact::class,
            'ParentController' => \App\Models\Parents::class, 'HistoriesController' => \App\Models\Histrories::class,
            'ProductController' => \App\Models\Product::class, 'OrderController' => \App\Models\Order::class,
            'ReceiptController' => \App\Models\Receipt::class, 'InvoiceController' => \App\Models\Invoice::class,
            'PositionController' => \App\Models\Position::class, 'TeachingPeriodController' => \App\Models\TeachingPeriod::class,
            'CourseController' => \App\Models\CoursePending::class,
        ];
        $model = $models[$controller] ?? null;
        if ($model) {
            foreach (['id', 'contact', 'parent', 'history', 'product', 'order', 'receipt', 'invoice', 'position', 'TeachingPeriod'] as $key) {
                $value = $request->route($key);
                if ($value instanceof \Illuminate\Database\Eloquent\Model) { $value = $value->getKey(); }
                if ($value !== null && ctype_digit((string) $value)) {
                    abort_unless($model::query()->whereKey($value)->exists(), 403, 'Record access denied.');
                }
            }
            $method = strtolower($request->route()->getActionMethod());
            if (str_contains($method, 'destroy') || str_contains($method, 'export') || str_ends_with($method, '_all')) {
                $ids = $request->input('table_records', $request->filled('id') ? [$request->id] : []);
                abort_unless(is_array($ids) && count($ids) <= 500, 422);
                if ($ids) {
                    foreach ($ids as $id) { abort_unless(is_scalar($id) && ctype_digit((string) $id), 422); }
                    abort_unless(count(array_unique($ids)) === $model::query()->whereKey($ids)->count(), 403, 'Bulk selection contains inaccessible records.');
                }
            }
        }
        foreach (['student' => \App\Models\Contact::class, 'cid' => \App\Models\Contact::class, 'student_id' => \App\Models\Contact::class, 'std_id' => \App\Models\Contact::class, 'teacher_id' => \App\Models\User::class] as $key => $class) {
            if ($request->filled($key) && is_scalar($request->input($key)) && ctype_digit((string) $request->input($key))) {
                abort_unless($class::query()->whereKey($request->input($key))->exists(), 403, 'Related record access denied.');
            }
        }
    }
}
