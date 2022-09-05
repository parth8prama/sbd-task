<?php

namespace App\Console\Commands;

use App\Jobs\GetModelJob;
use Exception;
use Illuminate\Console\Command;

// main-job
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RunScrap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Run:scrap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap vehicle information';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            // GetModelJob::dispatch();
            $response = Http::get('https://shop.ford.com/showroom');
            $response = $response->body();
            $response_slice = '';
            $models = [];
            if(!empty($response)){
                $response_slice = Str::between($response, '"nameplatePricing":', ',"priceLabel"');
                $models = json_decode($response_slice);
                foreach ($models as $modelKey => $model) {
                    // dispatch inventory job from here
                    GetModelJob::dispatch($model);
                }
            }
            return 1;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return 1;
        }
    }
}
