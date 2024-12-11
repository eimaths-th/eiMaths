<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function webhook(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!$data || empty($data['events'])) {
            Log::warning('Invalid or empty webhook payload', ['content' => $content]);
            return response()->json(['status' => 'ignored'], 200);
        }

        foreach ($data['events'] as $event) {
            Log::info('Webhook Event', ['event' => $event]);

            if (isset($event['replyToken']) && $event['replyToken'] !== '00000000000000000000000000000000') {
                $this->replyMessage($event['replyToken'], 'Hello!');
            } else {
                Log::warning('Reply token is invalid or placeholder.', ['event' => $event]);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function replyMessage($replyToken, $message)
    {
        $data = [
            'replyToken' => $replyToken,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message,
                ],
            ],
        ];

        Log::info('Payload Sent to LINE API', ['payload' => $data]);

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->post('https://api.line.me/v2/bot/message/reply', [
                'headers' => [
                    'Authorization' => 'Bearer ' . 'vlVj1G9iO1AO690/AcdzqRP9WXvkSCaZdZ2bAbUKI13SpgHY4lB1NPuBr4U+dJld+e4o0EOqwbmyudz61unaWxWP//j+gbGbSsDEyOVuMpmd76BXXnVz1i3jpJPA/QU/U+hgxPMtYaFrX8h/XJkXugdB04t89/1O/w1cDnyilFU=',
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            Log::info('Line Reply Sent', ['response' => $response->getBody()]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error('Line Reply Error', ['message' => $e->getMessage(), 'payload' => $data]);
        }
    }




    private function sendReply($replyToken, $message)
    {
        // Initialize Guzzle client
        $client = new Client();

        // Prepare the payload
        $data = [
            'replyToken' => $replyToken,
            'messages' => [$message],
        ];

        // Send the request to Line API
        $response = $client->post('https://api.line.me/v2/bot/message/reply', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'), // Your LINE Channel access token
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . 'vlVj1G9iO1AO690/AcdzqRP9WXvkSCaZdZ2bAbUKI13SpgHY4lB1NPuBr4U+dJld+e4o0EOqwbmyudz61unaWxWP//j+gbGbSsDEyOVuMpmd76BXXnVz1i3jpJPA/QU/U+hgxPMtYaFrX8h/XJkXugdB04t89/1O/w1cDnyilFU=',

            ],
            'json' => $data,
        ]);

        // Log the response
        Log::info('Line API response', ['response' => $response->getBody()->getContents()]);
    }
}
