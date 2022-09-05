<?php

namespace App\Jobs;

use App\Helper;
use App\Models\Locations;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class GetModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $model;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            // store for future use
            if(!(VehicleModel::where('model_name',$this->model->model)->where('year',$this->model->year)->count())) {
                VehicleModel::create([
                    'model_name' => $this->model->model,
                    'year' => $this->model->year,
                    'segment' => isset($this->model->seg)?:'',
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
                    "model" => $this->model->model,
                    "zipcode" => $location->zipcode,
                ]);
                $dealer_response = $dealer_response->body();
                if(!empty($dealer_response)){
                    $dealer_data = json_decode($dealer_response);
                    $dealerSlug = !empty($dealer_data->data) && !empty($dealer_data->data->firstFDDealerSlug)? $dealer_data->data->firstFDDealerSlug : '';

                    // get dealer-lot
                    $vehical_response = Http::get('https://shop.ford.com/aemservices/cache/inventory/dealer-lot', [
                        "dealerSlug" => $dealerSlug,
                        "make" => "Ford",
                        "market" => "US",
                        "Order" => "Distance",
                        "Radius" => "100",
                        "inventoryType" => "Radius",
                        "model" => $this->model->model,
                        "year" => $this->model->year,
                        "zipcode" => $location->zipcode,
                    ]);

                    $vehical_body = $vehical_response->body();
                    if(!empty($vehical_body)){
                        $vehicalObj = json_decode($vehical_body);
                        if(isset($vehicalObj->status) && $vehicalObj->status == 'success' && !empty(@$vehicalObj->data->filterResults->ExactMatch->vehicles)){
                            $vehicals = $vehicalObj->data->filterResults->ExactMatch->vehicles;
                            foreach ($vehicals as $vehical) {
                                $nodeValueArray = [];
                                // fetch pdf
                                $pdf_url = "http://www.windowsticker.forddirect.com/windowsticker.pdf?vin=$vehical->vin";
                                $fileName = Helper::storePdf($vehical->vin, $pdf_url);
                                if($fileName && Storage::disk('public')->exists($fileName)){
                                    $fileurl = Storage::disk('public')->path($fileName);

                                    $pdf = new \TonchikTm\PdfToHtml\Pdf($fileurl, [
                                        'pdftohtml_path' => 'C:\Users\Dell\Downloads\poppler\bin\pdftohtml.exe',
                                        'pdfinfo_path' => 'C:\Users\Dell\Downloads\poppler\bin\pdfinfo.exe',
                                        'generate' => [ // settings for generating html
                                            'singlePage' => true, // we want separate pages
                                            'ignoreImages' => true, // we need images
                                        ],
                                        'html' => [ // settings for processing html
                                            'inlineCss' => true, // replaces css classes to inline css rules
                                            'inlineImages' => true, // looks for images in html and replaces the src attribute to base64 hash
                                            'onlyContent' => true, // takes from html body content only
                                        ]
                                    ]);

                                    // get content from one page
                                    $contentFirstPage = $pdf->getHtml()->getPage(1);

                                    $crawler = new Crawler($contentFirstPage);
                                    $nodeValueArray = $crawler->filter('p')->each(function (Crawler $node, $i) {
                                        return $node->text();
                                    });
                                }
                                Vehicle::create([
                                    'vin' => $vehical->vin,
                                    'model' => @$vehical->model->ngpModelName,
                                    'year' => $vehical->year,
                                    'make' => @$vehical->model->make,
                                    'trim' => $vehical->trimId,
                                    'style' => @$vehical->model->ngpVehicleType,
                                    'pdf_data' => $nodeValueArray,
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
