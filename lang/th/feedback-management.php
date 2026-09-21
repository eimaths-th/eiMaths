<?php

return [
    'list' => [
        'page_title' => 'รายการข้อเสนอแนะ',
        'new' => 'เพิ่มข้อเสนอแนะ',
        'card_title' => 'รายการข้อเสนอแนะ',
        'collapse' => 'ย่อ',
        'table' => [
            'number' => 'ลำดับ',
            'title' => 'หัวข้อ',
            'category' => 'หมวดหมู่',
            'status' => 'สถานะ',
            'priority' => 'ความสำคัญ',
            'action' => 'การดำเนินการ',
        ],
        'view' => 'ดู',
    ],
    'create' => [
        'page_title' => 'สร้างข้อเสนอแนะ',
        'card_title' => 'สร้างข้อเสนอแนะ',
        'collapse' => 'ย่อ',
        'fields' => [
            'title' => 'หัวข้อ:',
            'description' => 'รายละเอียด:',
            'category' => 'หมวดหมู่:',
            'priority' => 'ความสำคัญ:',
        ],
        'categories' => [
            'bug' => 'ข้อผิดพลาด',
            'suggestion' => 'ข้อเสนอแนะ',
            'other' => 'อื่น ๆ',
        ],
        'priorities' => [
            'low' => 'ต่ำ',
            'medium' => 'ปานกลาง',
            'high' => 'สูง',
        ],
        'submit' => 'ส่งข้อเสนอแนะ',
    ],
    'detail' => [
        'page_title' => 'รายละเอียดข้อเสนอแนะ',
        'card_title' => 'ดูข้อเสนอแนะ',
        'collapse' => 'ย่อ',
        'fields' => [
            'description' => 'รายละเอียด:',
            'category' => 'หมวดหมู่:',
            'status' => 'สถานะ:',
            'priority' => 'ความสำคัญ:',
            'submitted_by' => 'ส่งโดย:',
            'admin_comments' => 'ความคิดเห็นของผู้ดูแลระบบ:',
            'attachment' => 'ไฟล์แนบ:',
            'update_status' => 'อัปเดตสถานะ:',
        ],
        'anonymous' => 'ไม่ระบุชื่อ',
        'none' => 'ไม่มี',
        'view_attachment' => 'ดูไฟล์แนบ',
        'status_options' => [
            'new' => 'ใหม่',
            'in_progress' => 'กำลังดำเนินการ',
            'resolved' => 'แก้ไขแล้ว',
        ],
        'update' => 'อัปเดตข้อเสนอแนะ',
        'back' => 'กลับไปรายการข้อเสนอแนะ',
    ],
    'categories' => [
        'bug' => 'ข้อผิดพลาด',
        'suggestion' => 'ข้อเสนอแนะ',
        'other' => 'อื่น ๆ',
    ],
    'status' => [
        'new' => 'ใหม่',
        'in_progress' => 'กำลังดำเนินการ',
        'resolved' => 'แก้ไขแล้ว',
    ],
    'priorities' => [
        'low' => 'ต่ำ',
        'medium' => 'ปานกลาง',
        'high' => 'สูง',
    ],
    'messages' => [
        'submitted' => 'ส่งข้อเสนอแนะสำเร็จ',
        'updated' => 'อัปเดตข้อเสนอแนะสำเร็จ',
    ],
];
