<?php

namespace App\Jobs;

use App\Models\Car;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class FetchCarInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $model;

    /**
     * Create a new job instance.
     */
    public function __construct($chatId, $model)
    {
        $this->chatId = $chatId;
        $this->model = $model;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Handling FetchCarInfo job', ['chat_id' => $this->chatId, 'model' => $this->model]);

        // Убедитесь, что модель - это строка
        $model = is_string($this->model) ? trim($this->model) : '';

        $cacheKey = "car_info_{$model}";
        $carInfo = Cache::get($cacheKey);

        if (!$carInfo) {
            $car = Car::query()->where('model', 'LIKE', "%{$this->model}%")
                ->orWhere('make_cyrillic_name', 'LIKE', "%{$this->model}%")
                ->orWhere('model_cyrillic_name', 'LIKE', "%{$this->model}%")
                ->first();
            if ($car) {
                $carInfo = "Модель: {$car->model}\nПроизводитель: {$car->make}\nПолное название: {$car->full_name}\nВ нашем авто парке имеется это авто, можете прийти и оформить авто прокат данной модели!!!\n";
            } else {
                $carInfo = "Извините, модель \"{$model}\" не найдена в нашей базе данных.";
            }

            Cache::put($cacheKey, $carInfo, now()->addMinutes(2));
        }

        $telegramService = new TelegramService();
        $telegramService->telegram_boot('sendMessage', [
            'text' => $carInfo,
            'chat_id' => $this->chatId,
        ]);
    }
}
