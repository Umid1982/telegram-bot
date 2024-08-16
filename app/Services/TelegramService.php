<?php

namespace App\Services;

use App\Jobs\FetchCarInfo;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $base_url;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->base_url = config('services.telegram.url_api');
    }

    public function handleHelpCommand($chat_id)
    {
        $helpText = "Доступные команды:\n";
        $helpText .= "/help - справка по командам\n";
        $helpText .= "/car - проверить наличие модели автомобиля\n";
        // Если планируются другие команды, можно добавить их здесь

        return $this->telegram_boot('sendMessage', [
            'text' => $helpText,
            'chat_id' => $chat_id,
        ]);
    }

    public function telegram_boot($method, $datas = [])
    {
        $token = $this->token;
        $queryString = http_build_query($datas);
        $url = $this->base_url . $token . "/" . $method . "?" . $queryString;
        return Http::get($url);

    }

    public function webhook(Request $request)
    {
        $update = $request->all();

        if (!isset($update['message']) || !isset($update['message']['chat']['id'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
        }

        $message = $update['message'];
        $chat_id = $message['chat']['id'];
        $text = trim($message['text'] ?? '');

        Log::info('Webhook received', ['chat_id' => $chat_id, 'text' => $text]);

        if ($text === '/help') {
            return $this->handleHelpCommand($chat_id);
        }

        if ($text === '/start') {
            return $this->telegram_boot('sendMessage', [
                'text' => "Добро пожаловать! Используйте команду /help для получения списка доступных команд.",
                'chat_id' => $chat_id,
            ]);
        }

        if (empty($text)) {
            return $this->telegram_boot('sendMessage', [
                'text' => "Пожалуйста, введите модель автомобиля.",
                'chat_id' => $chat_id,
            ]);
        }

        FetchCarInfo::dispatch($chat_id, $text);

        return $this->telegram_boot('sendMessage', [
            'text' => "Запрос на получение информации о модели отправлен. Я пришлю вам информацию, как только получу ответ.",
            'chat_id' => $chat_id,
        ]);
    }
}
//https://api.telegram.org/bot6598185466:AAHbdSeUh_pU2urM9r2i9Yhm-GQlvvaD22k/setWebhook?url=https://oohpjo94np.sharedwithexpose.com/api/webhook
//https://api.telegram.org/bot6598185466:AAHbdSeUh_pU2urM9r2i9Yhm-GQlvvaD22k/getWebhookInfo

