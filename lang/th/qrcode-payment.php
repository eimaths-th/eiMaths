<?php

return [
    'navigation' => ['title' => 'จัดการการชำระเงิน QR'],
    'fields' => [
        'account_name' => 'ชื่อบัญชี',
        'bank_name' => 'ชื่อธนาคาร',
        'account_number' => 'เลขที่บัญชี',
        'promptpay_number' => 'หมายเลขพร้อมเพย์',
    ],
    'actions' => ['update' => 'อัปเดต'],
    'accessibility' => ['collapse' => 'ย่อ'],
    'flash' => ['updated' => 'อัปเดต QR Code สำเร็จแล้ว'],
];
