<?php

namespace App\Http\Controllers;

use App\Services\AI\ShoppingAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AssistantController extends Controller
{
    private const SESSION_KEY = 'shopping_assistant_history';

    private const MAX_HISTORY = 20;

    public function __construct(
        private readonly ShoppingAssistantService $assistant,
    ) {}

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $history = session(self::SESSION_KEY, []);

        try {
            $result = $this->assistant->reply(
                $request->user(),
                $history,
                $validated['message'],
            );
        } catch (Throwable $e) {
            Log::error('Shopping assistant error: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'reply' => 'Xin lỗi, trợ lý đang gặp sự cố kỹ thuật. Vui lòng thử lại sau ít phút.',
            ], 200);
        }

        session([self::SESSION_KEY => array_slice($result['history'], -self::MAX_HISTORY)]);

        return response()->json(['reply' => $result['reply']]);
    }

    public function reset(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['success' => true]);
    }
}
