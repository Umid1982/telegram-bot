<?php

namespace App\Console\Commands;

use App\Console\Constants\CommandAlias;
use App\Models\Car;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateCarsDataBaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cars:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $base_url;

    public function __construct()
    {
        parent::__construct();
        $this->base_url = config('services.car_api.url');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get("$this->base_url/api/cars?full=1");

        $response->collect()->each(function (array $brand) {
            foreach ($brand['models'] as $model) {
                Car::query()->updateOrInsert([
                    'model' => $model['name'],
                    'make' => $brand['name']
                ], [
                    'full_name' => $brand['name'] . ' ' . $model['name'],
                    'model_cyrillic_name' => $model['cyrillic-name'],
                    'make_cyrillic_name' => $brand['cyrillic-name']
                ]);
            }
        });

        return CommandAlias::SUCCESS;
    }
}
