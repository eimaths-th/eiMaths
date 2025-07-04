<?php
if (!function_exists('thaiDate')) {
    function thaiDate($format, $timestamp = null)
    {
        // your logic here
        $months = [
            'มกราคม',
            'กุมภาพันธ์',
            'มีนาคม',
            'เมษายน',
            'พฤษภาคม',
            'มิถุนายน',
            'กรกฎาคม',
            'สิงหาคม',
            'กันยายน',
            'ตุลาคม',
            'พฤศจิกายน',
            'ธันวาคม'
        ];

        if ($timestamp === null) {
            $timestamp = time();
        }

        $formattedDate = date($format, $timestamp);

        $formattedDate = str_replace('F', $months[date('n', $timestamp) - 1], $formattedDate);
        $formattedDate = str_replace('M', mb_substr($months[date('n', $timestamp) - 1], 0, 3), $formattedDate);

        return $formattedDate;
    }
}


