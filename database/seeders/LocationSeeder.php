<?php

namespace Database\Seeders;

use App\Models\Locations;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Locations::insert([
            [
                "zipcode" => "51357",
                "let" => "-95.27240814598035",
                "lng" => "43.05227133672021",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "zipcode" => "14441",
                "let" => "-76.9572309419725",
                "lng" => "42.68648675635556",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "zipcode" => "74369",
                "let" => "-95.1598070855156",
                "lng" => "36.923608369529454",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "zipcode" => "17536",
                "let" => "-76.07499207262171",
                "lng" => "39.84106894879242",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "zipcode" => "66510",
                "let" => "-95.62754458089918",
                "lng" => "38.50314539499216",
                "created_at" => now(),
                "updated_at" => now(),
            ]
        ]);
    }
}
