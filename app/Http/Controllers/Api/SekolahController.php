<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;

class SekolahController extends Controller
{
    public function index()
    {
        return Sekolah::select(
                'id',
                'nama',
                'npsn',
                'jenjang',
                'status'
            )
            ->orderBy('nama')
            ->get();
    }
}