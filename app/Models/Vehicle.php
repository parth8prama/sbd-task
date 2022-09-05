<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vin',
        'model',
        'year',
        'make',
        'trim',
        'style',
        'pdf_data',
    ];

    protected $casts = [
        'pdf_data' => 'json',
    ];

}
