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
use App\Models\OpsiJawaban;
use App\Models\BanksoalPernyataan;

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
                    $status = null;
                } elseif ($attempt->selesai) {
                    $status = 'submitted';
                } else {
                    $status = 'ongoing';
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
        
        // return response()->json([
        //     'data' => $tryoutSoal
        // ]);
        

        $tryoutSoal = TryoutSoal::where('tryout_id', $id)
            ->orderBy('urutan')
            ->get()
            ->get($request->number - 1);
            
        // return $tryoutSoal;
        // return $tryoutSoal->get($number - 1);
        // return $request->number;

        $bankSoal = BankSoal::findOrFail($tryoutSoal->banksoal_id);
        // return $bankSoal;

        $tipe = $bankSoal->tipe;
        $opsi = null;   

        if ($tipe === 'pg') {
            // pilihan ganda biasa → ambil dari opsi_jawaban
            $opsi = OpsiJawaban::where('soal_id', $bankSoal->id)
                ->orderBy('label')
                ->get()
                ->map(function ($o) {
                    return [
                        'key' => $o->label,
                        'text' => $o->teks,
                    ];
                })
                ->values();
        }
        // return $opsi;

        if ($tipe === 'pg_kompleks') {
            // pilihan ganda kompleks → ambil dari banksoal_pernyataan
            $opsi = BanksoalPernyataan::where('banksoal_id', $bankSoal->id)
                ->orderBy('urutan')
                ->get()
                ->map(function ($p) {
                    return [
                        'key' => $p->urutan,
                        'text' => $p->teks,
                    ];
                })
                ->values();
        }

        // return $opsi;

        $totalSoal = TryoutSoal::where('tryout_id', $id)->count();

        // return $totalSoal;


        // ambil jawaban user jika ada
        $jawaban = JawabanPeserta::where('attempt_id', $attempt->id)
            ->where('banksoal_id', $bankSoal->id)
            ->value('jawaban');
        
        // $jawaban = JawabanPeserta::where('attempt_id', $attempt->id)
        //     ->where('banksoal_id', $bankSoal->id)
        //     ->value('jawaban');

            // return json_decode($jawaban, true);
            // return $jawaban;
            // return $attempt->id . ' - ' . $bankSoal->id;

        return response()->json([
            'data' => [
                'pertanyaan' => $bankSoal->pertanyaan,
                'tipe' => $tipe,
                'opsi' => $opsi,
                // 'jawaban' => $jawaban ? json_decode($jawaban, true) : [],
                'jawaban' => $jawaban ?? [],
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
                'attempt_id'  => $attempt->id,
                'banksoal_id' => $tryoutSoal->banksoal_id,
            ],
            [
               'jawaban' => array_values($request->jawaban),            
            ]
        );
        

        return response()->json([
            'message' => 'Jawaban tersimpan'
        ]);
    }

    public function finish($id)
    {
        $user = Auth::user();

        // 1. Ambil attempt aktif
        $attempt = Attempt::where('tryout_id', $id)
            ->where('user_id', $user->id)
            ->whereNull('selesai')
            ->first();

        if (! $attempt) {
            return response()->json([
                'message' => 'Tryout sudah diakhiri atau tidak ditemukan'
            ], 400);
        }

        // 2. Kunci attempt
        $attempt->update([
            'selesai' => now(),
            'status'  => 'submitted', // opsional, tapi disarankan
        ]);

        // 3. (OPSIONAL) Hitung nilai
        // $this->hitungNilai($attempt);

        return response()->json([
            'message' => 'Tryout berhasil diakhiri'
        ]);
    }
}