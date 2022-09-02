<?php

namespace App\Console\Commands;

use App\Jobs\GetModelJob;
use App\Models\Locations;
use App\Models\VehicleModel;
use Illuminate\Console\Command;

// main-job
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

// inventory-job

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

                // store for future use
                if(!(VehicleModel::where('model_name',$model->model)->where('year',$model->year)->count())) {
                    VehicleModel::create([
                        'model_name' => $model->model,
                        'year' => $model->year,
                        'segment' => isset($model->seg)?:'',
                    ]);
                }

                //  Inventory scraping
                $locations = Locations::get();
                foreach($locations as $location){
                    // Get dealerSlug
                    $dealer_response = Http::get('https://shop.ford.com/aemservices/cache/inventory/dealer/dealers', [
                        "make" => "Ford",
                        "market" => "US",
                        "inventoryType" => "Radius",
                        "maxDealerCount" => "10",
                        "model" => $model->model,
                        "zipcode" => $location->zipcode,
                    ]);
                    $dealer_response = $dealer_response->body();
                    if(!empty($dealer_response)){
                        $dealer_data = json_decode($dealer_response);
                        dd(urldecode($dealer_data->data->firstFDDealerSlug));
                    }

                }

            }
        }
        dd($models);

        return 1;
    }
}
