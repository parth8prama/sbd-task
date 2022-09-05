<?php
namespace App;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Helper
{
    public static function storePdf(string $vin, string $url)
    {
        try{
            $data = file_get_contents($url);
            $fileName = $vin.'.pdf';
            Storage::disk('public')->put($fileName, $data);
            return $fileName;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
