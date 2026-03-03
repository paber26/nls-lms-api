<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attempt;
use App\Models\Tryout;

class LeaderboardController extends Controller
{
    // 1. Ambil daftar tryout yang sudah ada
    public function tryouts(Request $request)
    {
        $query = Tryout::query()
            ->whereIn('status', ['active', 'finished']);

        // Optional filter ?status=active or ?status=finished
        if ($request->filled('status') && in_array($request->status, ['active', 'finished'])) {
            $query->where('status', $request->status);
        }

        $tryouts = $query
            ->select('id', 'paket', 'mulai', 'selesai', 'status')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($tryouts);
    }

    // 2. Ambil leaderboard berdasarkan tryout tertentu
    public function leaderboard($tryoutId)
    {
        $data = Attempt::query()
            ->join('users', 'users.id', '=', 'attempt.user_id')
            ->where('attempt.tryout_id', $tryoutId)
            ->where('attempt.status', 'submitted')
            ->orderByDesc('attempt.nilai')
            ->select(
                'users.id as user_id',
                'users.name',
                'users.sekolah_nama',
                'attempt.nilai'
            )
            ->get();

        $ranking = $data->values()->map(function ($item, $index) {
            $item->peringkat = $index + 1;
            return $item;
        });

        return response()->json($ranking);
    }
}