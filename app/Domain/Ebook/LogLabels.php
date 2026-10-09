<?php

namespace App\Domain\Ebook;

final class LogLabels
{
    public static function action(string $action): string
    {
        return __('ebooks.log_actions')[$action] ?? $action;
    }

    public static function detail(object $event, array $pageNumbers = []): string
    {
        $metadata = json_decode($event->metadata ?? '{}', true) ?: [];
        if (isset($metadata['source_page_id'])) {
            return isset($pageNumbers[$metadata['source_page_id']])
                ? __('ebooks.log_details.source_page', ['number' => $pageNumbers[$metadata['source_page_id']]])
                : __('ebooks.log_details.source_original');
        }
        if (isset($metadata['objects'])) { return trans_choice('ebooks.counts.objects', $metadata['objects']); }
        if (isset($metadata['version'])) { return __('ebooks.edition', ['number' => $metadata['version']]); }
        if (isset($metadata['platform'])) { return ucfirst($metadata['platform']); }
        return match ($metadata['reason'] ?? '') {
            'focus_lost' => __('ebooks.log_details.focus_lost'),
            'active_session' => __('ebooks.log_details.active_session'),
            'revision_conflict' => __('ebooks.log_details.revision_conflict'),
            'connection_or_server' => __('ebooks.log_details.connection_or_server'),
            default => '—',
        };
    }

    public static function conversionError(string $error): string
    {
        // Older conversion jobs stored Thai messages. Translate known messages at display time.
        if ($error === 'อ่านจำนวนหน้า PDF ไม่สำเร็จ') { return __('ebooks.conversion.page_count'); }
        if ($error === 'กรุณาใช้ PDF ที่ไม่ติดรหัสผ่าน') { return __('ebooks.conversion.password'); }
        if (preg_match('/^รองรับ PDF ไม่เกิน (\d+) หน้า$/u', $error, $match)) {
            return __('ebooks.conversion.page_limit', ['count' => $match[1]]);
        }
        if (preg_match('/^แปลงภาพหน้า (\d+) ไม่สำเร็จ$/u', $error, $match)) {
            return __('ebooks.conversion.page_failed', ['number' => $match[1]]);
        }
        return $error;
    }
}
