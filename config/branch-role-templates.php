<?php

// Explicit baseline, applied only by branch:roles --apply. Existing roles are not
// overwritten merely by deploying code or migrating the database.
$crud = fn (string $prefix) => array_map(fn ($action) => $prefix.'-'.$action, ['list', 'create', 'edit', 'delete']);
$teacher = ['dashboard', 'student-list', 'parent-list', 'history-list', 'history-create', 'history-edit', 'teaching-list', 'quiz-list', 'quiz-manage'];
$branch = array_values(array_unique(array_merge(
    $teacher, ...array_map($crud, ['student', 'parent', 'history', 'teaching', 'user', 'role', 'department', 'product', 'order', 'receipt', 'invoice', 'affiliate-course-application'])
)));
$branch = array_merge($branch, [
    'centre-manage', 'course-list', 'order-show', 'receipt-show', 'invoice-show', 'product-show',
    'student-export', 'receipt-export', 'order-export', 'invoice-export',
    'crm-leads-menu-view', 'crm-leads-create', 'crm-leads-edit', 'crm-leads-delete',
    'credit-note-list', 'credit-note-show', 'credit-note-create', 'credit-note-edit', 'credit-note-issue', 'credit-note-cancel',
]);

return [
    'Teacher' => $teacher,
    'BranchAdmin' => $branch,
    'Admin' => array_values(array_unique(array_merge($branch, [
        'all-centre', 'centre-create', 'centre-edit', 'centre-delete', 'branch-modules-manage',
        'branch-transfer', 'central-settings-manage', 'financial-settings-manage',
        'integration-settings-manage', 'support-manage', 'course-manage',
    ]))),
];
