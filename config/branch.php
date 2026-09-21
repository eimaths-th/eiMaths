<?php

return [
    'transfers' => [
        'page_size' => 25,
        'max_batch_size' => 100,
    ],

    // Names remain compatible with the existing web permission catalog.
    'modules' => [
        'dashboard' => 'ภาพรวม', 'students' => 'นักเรียนและผู้ปกครอง',
        'teaching' => 'ประวัติและตารางเรียน', 'finance' => 'การเงิน',
        'stock' => 'สต็อกและหนังสือ', 'crm' => 'CRM และการสมัครเรียน',
        'quiz' => 'แบบทดสอบ', 'users' => 'ผู้ใช้และบทบาท',
        'departments' => 'แผนก', 'content' => 'บทความและคำถามที่พบบ่อย',
    ],
    'default_modules' => ['dashboard', 'students', 'teaching', 'finance', 'stock', 'users', 'departments'],
    'permission_modules' => [
        'dashboard' => 'dashboard', 'student-' => 'students', 'parent-' => 'students',
        'history-' => 'teaching', 'teaching-' => 'teaching', 'view-log' => 'teaching',
        'order-' => 'finance', 'receipt-' => 'finance', 'invoice-' => 'finance', 'credit-note-' => 'finance',
        'product-' => 'stock', 'book-' => 'stock', 'crm-' => 'crm',
        'affiliate-course-application-' => 'crm',
        'quiz-' => 'quiz', 'user-' => 'users', 'role-' => 'users', 'department-' => 'departments',
        'blog-' => 'content', 'faq-' => 'content',
    ],
    'central_permissions' => [
        'all-centre', 'centre-create', 'centre-edit', 'centre-delete', 'branch-modules-manage',
        'branch-transfer', 'central-settings-manage', 'financial-settings-manage',
        'integration-settings-manage', 'support-manage', 'course-manage',
    ],
    'owner_permissions' => ['add-permission', 'edit-permission', 'delete-permission', 'permission-manage'],
    // Affiliate administration is HQ-only except for course applications, which
    // branch staff manage within the existing branch/CRM data scope.
    'branch_scoped_affiliate_permission_prefixes' => ['affiliate-course-application-'],
    'protected_roles' => ['SystemAdmin', 'Admin', 'BranchAdmin', 'Teacher', 'User', 'Affiliate-user'],
    // Explicit relationships used for reads, writes and mixed-record bulk requests.
    'models' => [
        App\Models\Department::class => ['column' => 'id'],
        App\Models\Position::class => ['column' => 'department_id'],
        App\Models\User::class => ['column' => 'department_id'],
        App\Models\Contact::class => ['column' => 'centre'],
        App\Models\Parents::class => ['column' => 'centre_id'],
        App\Models\Histrories::class => ['column' => 'centre'],
        App\Models\LogHistory::class => ['column' => 'centre'],
        App\Models\Order::class => ['column' => 'centre'],
        App\Models\Receipt::class => ['column' => 'centre'],
        App\Models\Invoice::class => ['column' => 'centre'],
        App\Models\Product::class => ['column' => 'centre'],
        App\Models\Stock::class => ['column' => 'centre'],
        App\Models\TeachingPeriod::class => ['column' => 'department_id'],
        App\Models\CoursePending::class => ['column' => 'department_id'],
        App\Models\CrmLead::class => ['column' => 'branch_id'],
        App\Models\TrackingLink::class => ['column' => 'branch_id'],
        App\Models\OrderDetail::class => ['parent' => App\Models\Order::class, 'key' => 'order_id'],
        App\Models\OrderDetailList::class => ['parent' => App\Models\Order::class, 'key' => 'order_id'],
        App\Models\receipt_details::class => ['parent' => App\Models\Receipt::class, 'key' => 'receipt_id'],
        App\Models\HistoryStudentImg::class => ['special' => 'history-image'],
        App\Models\ContactQuiz::class => ['parent' => App\Models\Contact::class, 'key' => 'contact_id'],
        App\Models\CrmLeadNote::class => ['parent' => App\Models\CrmLead::class, 'key' => 'lead_id'],
        App\Models\FileStore::class => ['parent' => App\Models\Order::class, 'key' => 'module_id'],
        App\Models\Test::class => ['special' => 'test'],
        App\Models\Answer::class => ['parent' => App\Models\Test::class, 'key' => 'test_id'],
        App\Models\Feedback::class => ['special' => 'feedback'],
        App\Models\TrackingLinkClick::class => ['parent' => App\Models\TrackingLink::class, 'key' => 'tracking_link_id'],
        App\Models\FileUpload::class => ['column' => 'branch_id', 'owner' => 'uploaded_by'],
    ],
];
