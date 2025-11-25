<?php

namespace App\Services;

use App\Models\TelegramSetting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $chatId;
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'verify' => false,
        ]);
    }

    /**
     * Initialize Telegram settings from database
     */
    protected function initSettings()
    {
        $setting = TelegramSetting::getActive();

        if ($setting) {
            try {
                $this->botToken = decrypt($setting->bot_token);
                $this->chatId = decrypt($setting->chat_id);
                return true;
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                Log::error('Failed to decrypt Telegram settings: ' . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Send message to Telegram
     *
     * @param string $message
     * @param string|null $parseMode (HTML, Markdown, MarkdownV2)
     * @return bool
     */
    public function sendMessage($message, $parseMode = 'HTML')
    {
        if (!$this->initSettings()) {
            Log::warning('Telegram settings not configured or inactive');
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $response = $this->client->post($url, [
                'json' => [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => $parseMode,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($result['ok'] ?? false) {
                Log::info('Telegram message sent successfully');
                return true;
            }

            Log::error('Telegram API error: ' . json_encode($result));
            return false;

        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send course pending notification to admin
     *
     * @param array $data
     * @return bool
     */
    public function sendCoursePendingNotification($data)
    {
        $appointmentDate = \Carbon\Carbon::parse($data['appointment_date'])
            ->locale('th')
            ->translatedFormat('l ที่ j F Y เวลา H:i น.');

        $message = "🔔 <b>แจ้งเตือน: มีผู้ปกครองสนใจสมัครเรียน</b>\n\n"
            . "👤 <b>ข้อมูลผู้ปกครอง</b>\n"
            . "• ชื่อ: {$data['name']}\n"
            . "• อีเมล: {$data['email']}\n"
            . "• เบอร์โทร: {$data['telp']}\n"
            . "• ความสัมพันธ์: " . ($data['type_parent'] === 'father' ? 'คุณพ่อ' : 'คุณแม่') . "\n\n"
            . "👦 <b>ข้อมูลนักเรียน</b>\n"
            . "• ชื่อ: {$data['student_name']} ({$data['student_nickname']})\n"
            . "• ระดับชั้น: {$data['grade']}\n\n"
            . "📚 <b>คอร์สที่สนใจ</b>\n"
            . "• คอร์ส: {$data['course_name']}\n"
            . "• สาขา: {$data['department_id']}\n"
            . "• วันเรียน: {$data['day']}\n"
            . "• เวลาเรียน: {$data['period']}\n"
            . "• ราคา: {$data['price']} บาท\n\n"
            . "📅 <b>วันนัดหมาย</b>\n"
            . "• {$appointmentDate}\n\n"
            . "⏰ เวลาที่ลงทะเบียน: " . now()->format('d/m/Y H:i:s');

        return $this->sendMessage($message);
    }
}
