<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tryout;

class MonitoringTryoutController extends Controller
{
    public function index()
    {
        // return 'oke';
        $tryouts = Tryout::select(
                'tryout.id',
                'tryout.paket',
                'tryout.mulai',
                'tryout.selesai',
                'tryout.status'
            )
            ->withCount([
                'attempts as total_peserta',
                'attempts as sedang_mengerjakan' => function ($q) {
                    $q->where('status', 'ongoing');
                },
                'attempts as sudah_selesai' => function ($q) {
                    $q->where('status', 'submitted');
                }
            ])
            ->orderByDesc('tryout.created_at')
            ->get();

        return response()->json($tryouts);
    }
}