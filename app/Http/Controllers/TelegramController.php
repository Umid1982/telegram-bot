<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function __construct(protected readonly TelegramService $telegramService)
    {
    }

    public function webhook(Request $request)
    {
        Log::info('Webhook received', ['data' => $request->all()]);
        $data = $this->telegramService->webhook($request);
        return $data;

    }
}

