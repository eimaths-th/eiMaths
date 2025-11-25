<?php

namespace App\Http\Controllers;

use App\Models\TelegramSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Services\TelegramService;

class TelegramSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $datas = TelegramSetting::all();

        $datas->each(function ($row, $index) {
            $row->no = $index + 1;
        });

        if ($request->ajax()) {
            $datas = TelegramSetting::query()->orderBy('id', 'desc');
            $datas->orderBy("id", "desc")->get();

            return datatables()->of($datas)
                ->addColumn('no', function ($row) {
                    return $row->no;
                })
                ->editColumn('description', function (TelegramSetting $datas) {
                    return $datas->description ?? '-';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('d/m/Y H:i') : '-';
                })
                ->editColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return '
                        <label class="toggle-switch">
                            <input type="checkbox" class="toggle-status-update" data-id="' . $row->id . '" ' . $checked . '>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>';
                })
                ->addColumn('action', function ($row) {
                    if (Gate::allows('user-edit')) {
                        $html = '<button type="button" class="btn btn-sm btn-warning btn-edit" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button> ';
                    } else {
                        $html = '<button type="button" class="btn btn-sm btn-warning disabled" data-toggle="tooltip" data-placement="bottom" title="You Not Have Permission"><i class="fa fa-edit"></i> Edit</button> ';
                    }

                    $html .= '<button type="button" data-rowid="' . $row->id . '" class="btn btn-sm btn-danger btn-delete"><i class="fa fa-trash"></i> Delete</button>';
                    $html .= ' <button type="button" data-id="' . $row->id . '" class="btn btn-sm btn-info btn-test"><i class="fa fa-paper-plane"></i> Test</button>';

                    return $html;
                })->rawColumns(['no', 'status', 'action'])->toJson();
        }

        return view('telegram_setting.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'bot_token' => 'required|string',
            'chat_id' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        $encryptedData = [
            'bot_token' => Crypt::encryptString($validatedData['bot_token']),
            'chat_id' => Crypt::encryptString($validatedData['chat_id']),
            'description' => $validatedData['description'] ?? null,
            'status' => 1,
        ];

        TelegramSetting::create($encryptedData);

        return response()->json(['success' => 'Telegram setting stored successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = TelegramSetting::find($id);

        if (!$data) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        try {
            $bot_token = Crypt::decryptString($data->bot_token);
            $chat_id = Crypt::decryptString($data->chat_id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['error' => 'Failed to decrypt data'], 500);
        }

        return response()->json([
            'data' => $data,
            'bot_token' => $bot_token,
            'chat_id' => $chat_id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'bot_token' => 'required|string',
            'chat_id' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        $encryptedData = [
            'bot_token' => Crypt::encryptString($validatedData['bot_token']),
            'chat_id' => Crypt::encryptString($validatedData['chat_id']),
            'description' => $validatedData['description'] ?? null,
        ];

        $data = TelegramSetting::find($id);
        if (!$data) {
            return response()->json(['error' => 'Record not found'], 404);
        }
        $data->update($encryptedData);

        return response()->json(['message' => 'Record updated successfully', 'data' => $data]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = TelegramSetting::find($id);

        if (!$data) {
            return response()->json([
                'error' => 'Record not found.'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ]);
    }

    /**
     * Update status of telegram setting
     */
    public function updateStatus(string $id, Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        // If the new status is "on" (1), set all others to "off" (0)
        if ($request->status == 1) {
            TelegramSetting::where('id', '!=', $id)->update(['status' => 0]);
        }

        $data = TelegramSetting::findOrFail($id);
        $data->status = $request->status;
        $data->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }

    /**
     * Test telegram notification
     */
    public function testNotification(string $id)
    {
        $data = TelegramSetting::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }

        // Temporarily set this as active for testing
        $originalStatus = $data->status;
        $data->status = 1;
        $data->save();

        try {
            $telegramService = new TelegramService();
            $result = $telegramService->sendMessage("🔔 <b>Test Notification</b>\n\nThis is a test message from EiMaths-TH.\nTimestamp: " . now()->format('d/m/Y H:i:s'));

            // Restore original status
            $data->status = $originalStatus;
            $data->save();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test notification sent successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test notification. Please check your bot token and chat ID.'
                ]);
            }
        } catch (\Exception $e) {
            // Restore original status
            $data->status = $originalStatus;
            $data->save();

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
