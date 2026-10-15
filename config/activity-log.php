<?php

use App\Models\{Contact, Department, Histrories, Invoice, Order, OrderDetail, OrderDetailList, Parents, Position, Receipt, TeachingPeriod, User, receipt_details};
use App\Domain\CreditNote\Models\{CreditNote, CreditNoteItem};
use Spatie\Permission\Models\{Permission, Role};

return [
    // Only these back-office workflows participate. Existing module logs are untouched.
    'controllers' => [
        'UserController' => 'users', 'RoleController' => 'users', 'PermissionsController' => 'users',
        'DepartmentController' => 'branches', 'PositionController' => 'branches', 'BranchTransferController' => 'branches',
        'ContactController' => 'students', 'DiscontinueController' => 'students', 'ParentController' => 'students',
        'HistoriesController' => 'teaching', 'TeachingPeriodController' => 'teaching',
        'OrderController' => 'finance', 'ReceiptController' => 'finance', 'InvoiceController' => 'finance',
        'CreditNoteController' => 'finance',
    ],
    // Only legacy database-only writes need an outer transaction. Other workflows keep
    // their existing commit/retry/file boundaries; observer rows use those transactions.
    'transactional_actions' => [
        'ContactController' => ['store', 'receipt', 'update', 'destroy', 'destroy_all',
            'gennerateStudentSave', 'resetStudentPassword', 'removeStudentPassword', 'sCoin'],
        'DiscontinueController' => ['restart', 'restart_all'],
        'PermissionsController' => ['store', 'update', 'destroy'],
        'PositionController' => ['store', 'update', 'destroy', 'destroy_all'],
        'ParentController' => ['store', 'update', 'destroy'],
        'InvoiceController' => ['generate'],
    ],
    // Explicit field allowlists; never serialize a whole model or request into an audit row.
    'models' => [
        User::class => ['module' => 'users', 'branch' => 'department_id', 'fields' => ['name', 'email', 'department_id', 'position_id', 'disable']],
        Role::class => ['module' => 'users', 'branch' => 'branch_id', 'fields' => ['name', 'display_name', 'branch_id']],
        Permission::class => ['module' => 'users', 'fields' => ['name', 'guard_name']],
        Department::class => ['module' => 'branches', 'branch' => 'id', 'fields' => ['name', 'code', 'status', 'enabled_modules', 'branch_type']],
        Position::class => ['module' => 'branches', 'branch' => 'department_id', 'fields' => ['name', 'department_id', 'status']],
        Contact::class => ['module' => 'students', 'branch' => 'centre', 'fields' => ['code', 'name', 'nickname', 'centre', 'ctype', 'type', 'school', 'gender', 'birth_date', 'telephone', 'level', 'term', 'bookuse', 'level_name', 'term_name', 'bookuse_name', 'level2', 'term2', 'bookuse2', 'start_date', 'start_term', 'free_course', 'discontinued', 'discontinued_date', 'discontinued_reason']],
        Parents::class => ['module' => 'students', 'branch' => 'centre_id', 'fields' => ['fname', 'lname', 'centre_id', 'student_id', 'telp', 'email', 'relationship', 'gender']],
        Histrories::class => ['module' => 'teaching', 'branch' => 'centre', 'fields' => ['centre', 'student_id', 'teacher_id', 'level_id', 'level_name', 'term', 'bookuse', 'course_remaining', 'date', 'stime', 'etime', 'start_date', 'end_date', 'comment', 'status']],
        TeachingPeriod::class => ['module' => 'teaching', 'branch' => 'department_id', 'fields' => ['department_id', 'name', 'day', 'period', 'date', 'start_time', 'end_time', 'stime', 'etime', 'status']],
        Order::class => ['module' => 'finance', 'branch' => 'centre', 'fields' => ['centre', 'cid', 'order_number', 'total_price', 'refund', 'register_fee', 'access_fee', 'orther_fee', 'discount', 'discount_book', 'status', 'payment', 'free_course', 'free_course_reason']],
        Receipt::class => ['module' => 'finance', 'branch' => 'centre', 'fields' => ['centre', 'cid', 'student_no', 'student_name', 'receipt_number', 'payment_date', 'oid', 'level', 'total_fee', 'start_term', 'status', 'payment']],
        Invoice::class => ['module' => 'finance', 'branch' => 'centre', 'fields' => ['centre', 'cid', 'invoice_number', 'invoice_date', 'student_no', 'student_name', 'oid', 'level', 'order_term', 'total_price', 'total_fee', 'status', 'payment']],
        OrderDetail::class => ['module' => 'finance', 'parent' => [Order::class, 'order_id'], 'fields' => ['order_id', 'product_id', 'pname', 'punit', 'quantity', 'price']],
        OrderDetailList::class => ['module' => 'finance', 'parent' => [Order::class, 'order_id'], 'fields' => ['order_id', 'level', 'term', 'book', 'price', 'bprice', 'bqty']],
        receipt_details::class => ['module' => 'finance', 'parent' => [Receipt::class, 'receipt_id'], 'fields' => ['receipt_id', 'des', 'unit', 'quantity', 'price', 'discount', 'tax', 'line_type', 'tax_treatment', 'vat_rate_basis_points']],
        CreditNote::class => ['module' => 'finance', 'branch' => 'centre_id', 'fields' => ['receipt_id', 'centre_id', 'status', 'number', 'currency', 'reason_code', 'reason_details', 'issue_date', 'issued_at', 'cancelled_at', 'cancellation_reason', 'original_grand_total_minor', 'corrected_grand_total_minor', 'difference_grand_total_minor']],
        CreditNoteItem::class => ['module' => 'finance', 'parent' => [CreditNote::class, 'credit_note_id'], 'fields' => ['credit_note_id', 'description', 'unit', 'line_type', 'tax_treatment', 'vat_rate_basis_points', 'original_quantity', 'original_unit_price_minor', 'original_discount_minor', 'original_line_total_minor', 'credit_quantity', 'credit_amount_minor', 'credit_taxable_base_minor', 'credit_vat_minor', 'credit_exempt_minor', 'credit_out_of_scope_minor']],
    ],
];
