<?php

return [
    'errors' => [
        'domain' => 'ไม่สามารถดำเนินการ Affiliate ได้',
        'financial_source_locked' => 'ข้อมูลการเงินนี้ถูกล็อกเนื่องจากมีหลักฐานค่าคอมมิชชันแล้ว',
        'state_conflict' => 'สถานะของรายการ Affiliate ไม่รองรับการดำเนินการนี้',
        'ownership_denied' => 'ไม่มีสิทธิ์เข้าถึงรายการ Affiliate นี้',
        'configuration_invalid' => 'การตั้งค่า Affiliate ไม่พร้อมใช้งานหรือไม่ถูกต้อง',
        'insufficient_balance' => 'ยอดคงเหลือที่ใช้ได้ไม่เพียงพอ',
        'negative_balance_withdrawal' => 'ไม่สามารถถอนเงินขณะยอดคงเหลือที่ใช้ได้ไม่เป็นบวก',
        'actor_separation' => 'ผู้อนุมัติและผู้จ่ายเงินต้องเป็นคนละคน',
        'idempotency_conflict' => 'Idempotency key นี้ถูกใช้กับรายการอื่นแล้ว',
        'password_confirmation_required' => 'กรุณายืนยันรหัสผ่านปัจจุบันก่อนเปลี่ยนรายละเอียดบัญชีรับเงิน',
        'payout_account_cooldown' => 'ไม่สามารถถอนเงินได้ชั่วคราวหลังจากเปลี่ยนบัญชีรับเงิน',
    ],
];
