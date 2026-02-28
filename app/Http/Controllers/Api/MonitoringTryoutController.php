<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Attempt;

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
    
    public function show($id)
    {
        $participants = Attempt::with([
                'user:id,name,email,sekolah_id,sekolah_nama',
                'user.sekolah:id,nama'
            ])
            ->where('tryout_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'name' => $attempt->user->name ?? '-',
                    'email' => $attempt->user->email ?? '-',
                    'sekolah_nama' => $attempt->user->sekolah->nama ?? ($attempt->user->sekolah_nama ?? '-'),
                    'status' => $attempt->status,
                    'nilai' => $attempt->nilai,
                    'mulai' => $attempt->mulai,
                    'selesai' => $attempt->selesai,
                ];
            });

        return response()->json($participants);
    }
}