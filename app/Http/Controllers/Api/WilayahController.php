<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
// use App\Models\Sekolah;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    public function provinsi()
    {
        return Http::get(
            'https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json'
        )->json();
    }

    public function kabupaten($provinsiId)
    {
        return Http::get(
            "https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$provinsiId}.json"
        )->json();
    }

    public function kecamatan($kabupatenId)
    {
        return Http::get(
            "https://emsifa.github.io/api-wilayah-indonesia/api/districts/{$kabupatenId}.json"
        )->json();
    }
}