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
            if(self::get_http_response_code($url) != "200"){
                return false;
            }
            $data = file_get_contents($url);
            $fileName = $vin.'.pdf';
            Storage::disk('public')->put($fileName, $data);
            return $fileName;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    public static function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }
}
