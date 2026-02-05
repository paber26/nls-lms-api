<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attempt;
use App\Models\TryoutSoal;
use App\Models\BankSoal;
use App\Models\JawabanPeserta;

class UserTryoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $tryouts = Tryout::where('status', 'active')
            ->withCount('questions')
            ->with([
                'attempts' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
                'mapel'
            ])
            ->get()
            ->map(function ($tryout) {
                $attempt = $tryout->attempts->first();

                if (! $attempt) {
                    $status = 'belum';
                } elseif ($attempt->selesai) {
                    $status = 'selesai';
                } else {
                    $status = 'berjalan';
                }

                return [
                    'id' => $tryout->id,
                    'nama' => $tryout->paket ?? $tryout->nama,
                    'jenjang' => $tryout->mapel->tingkat ?? '-',
                    'mapel' => $tryout->mapel->nama ?? '-',
                    'jumlah_soal' => $tryout->questions_count ?? 0,
                    'durasi' => $tryout->durasi_menit ?? $tryout->durasi,
                    'status' => $status,
                ];
            });

        return response()->json([
            'data' => $tryouts
        ]);
    }

    public function show($id)
    {
        $tryout = Tryout::where('status', 'active')
            ->withCount('questions')
            ->with('mapel')
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $tryout->id,
                'nama' => $tryout->paket ?? $tryout->nama,
                'jenjang' => $tryout->mapel->tingkat ?? '-',
                'mapel' => $tryout->mapel->nama ?? '-',
                'jumlah_soal' => $tryout->questions_count ?? 0,
                'durasi' => $tryout->durasi_menit ?? $tryout->durasi,
            ]
        ]);
    }

    public function start($id)
    {
        $user = Auth::user();

        $tryout = Tryout::where('status', 'active')->findOrFail($id);

        $attempt = $tryout->attempts()
            ->where('user_id', $user->id)
            ->whereNull('selesai')
            ->first();

        if (! $attempt) {
            $attempt = $tryout->attempts()->create([
                'tryout_id' => $tryout->id,
                'user_id' => $user->id,
                'mulai' => now(),
                'status' => 'ongoing',
            ]);
        }

        return response()->json([
            'message' => 'Tryout started',
            'attempt_id' => $attempt->id
        ]);
    }
    
    public function questions(Request $request, $id)
    {
        $user = Auth::user();
        $number = (int) $request->get('number', 1);

        // pastikan attempt aktif
        $attempt = Attempt::where('tryout_id', $id)
            ->where('user_id', $user->id)
            ->whereNull('selesai')
            ->firstOrFail();

        // ambil soal sesuai urutan
        $tryoutSoal = TryoutSoal::where('tryout_id', $id)
            ->orderBy('urutan')
            ->skip($number - 1)
            ->firstOrFail();
            

        $bankSoal = BankSoal::findOrFail($tryoutSoal->banksoal_id);
        // return $bankSoal;

        $totalSoal = TryoutSoal::where('tryout_id', $id)->count();

        // ambil jawaban user jika ada
        $jawaban = JawabanPeserta::where('attempt_id', $attempt->id)
            ->where('banksoal_id', $bankSoal->id)
            ->value('jawaban');

        return response()->json([
            'data' => [
                'pertanyaan' => $bankSoal->pertanyaan,
                'opsi' => json_decode($bankSoal->opsi, true),
                'jawaban' => $jawaban ? json_decode($jawaban, true) : [],
                'peserta' => $user->name,
                'total_soal' => $totalSoal,
            ]
        ]);
    }

    public function answer(Request $request, $id)
    {
        $request->validate([
            'nomor' => 'required|integer|min:1',
            'jawaban' => 'array'
        ]);

        $user = Auth::user();

        $attempt = Attempt::where('tryout_id', $id)
            ->where('user_id', $user->id)
            ->whereNull('selesai')
            ->firstOrFail();

        $tryoutSoal = TryoutSoal::where('tryout_id', $id)
            ->orderBy('urutan')
            ->skip($request->nomor - 1)
            ->firstOrFail();

        JawabanPeserta::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'banksoal_id' => $tryoutSoal->banksoal_id,
            ],
            [
                'jawaban' => json_encode($request->jawaban),
            ]
        );

        return response()->json([
            'message' => 'Jawaban tersimpan'
        ]);
    }
}