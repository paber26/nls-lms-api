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
        
        $bankSoal = BankSoal::find($tryoutSoal->banksoal_id);

        $isCorrect = null;
        $jawabanUser = array_values($request->jawaban);

        /*
        |--------------------------------------------------------------------------
        | Tentukan is_correct sesuai tipe soal
        |--------------------------------------------------------------------------
        */
        if ($bankSoal->tipe === 'isian') {
            if (
                isset($bankSoal->jawaban) &&
                isset($jawabanUser[0]) &&
                strtolower(trim($jawabanUser[0])) === strtolower(trim($bankSoal->jawaban))
            ) {
                $isCorrect = 1;
            } else {
                $isCorrect = 0;
            }
        }

        if ($bankSoal->tipe === 'pg') {
            if (isset($jawabanUser[0])) {
                $opsi = OpsiJawaban::where('soal_id', $bankSoal->id)
                    ->where('label', $jawabanUser[0])
                    ->first();

                $isCorrect = ($opsi && $opsi->is_correct) ? 1 : 0;
            } else {
                $isCorrect = 0;
            }
        }

        if ($bankSoal->tipe === 'pg_kompleks') {
            $pernyataan = BanksoalPernyataan::where('banksoal_id', $bankSoal->id)
                ->orderBy('urutan')
                ->get();

            $jumlahBenar = 0;
            
            foreach ($pernyataan as $p) {
                if (
                    isset($jawabanUser[((int) $p->urutan) - 1]) &&
                    (int) $jawabanUser[((int) $p->urutan) - 1] === (int) $p->jawaban_benar
                ) {
                    $jumlahBenar++;
                }
            }
            // pg kompleks dianggap benar jika semua pernyataan benar
            $isCorrect = ($jumlahBenar === 4) ? 1 : 0;
        }

        JawabanPeserta::updateOrCreate(
            [
                'attempt_id'  => $attempt->id,
                'banksoal_id' => $tryoutSoal->banksoal_id,
            ],
            [
                'jawaban'    => $jawabanUser,
                'is_correct' => $isCorrect,
            ]
        );
        

        return response()->json([
            'message' => 'Jawaban tersimpan'
        ]);
    }

    public function hasil($tryoutId)
    {
        $attempt = Attempt::with([
            'tryout',
            'jawabanPeserta'
        ])
        ->where('tryout_id', $tryoutId)
        ->where('user_id', Auth::user()->id)
        ->where('status', 'submitted')
        ->latest('selesai')
        ->firstOrFail();

        $jawaban = $attempt->jawabanPeserta;
        $nilaiPoin = 0;
        
        $benar = 0;
        $salah = 0;
        $kosong = 0;
        $navigasi = [];

        foreach ($jawaban as $index => $j) {
            if ($j->is_correct === null) {
                $kosong++;
                $status = 'kosong';
            } elseif ((int) $j->is_correct === 1) {
                $benar++;
                $status = 'benar';
            } else {
                $salah++;
                $status = 'salah';
            }

            $bankSoal = BankSoal::find($j->banksoal_id);
            if (! $bankSoal) continue;

            $tryoutSoal = TryoutSoal::where('tryout_id', $attempt->tryout_id)
                ->where('banksoal_id', $bankSoal->id)
                ->first();

            $poinSoal = (float) ($tryoutSoal->poin ?? 0);

            // return $j;
            // $jawabanUser = json_decode($j->jawaban, true) ?? [];
            $jawabanUser = $j->jawaban ?? [];
            // return $jawabanUser;

            /*
            |--------------------------------------------------------------------------
            | ISIAN → pakai tryout_soal.poin
            |--------------------------------------------------------------------------
            */
            if ($bankSoal->tipe === 'isian') {
                if ((int) $j->is_correct === 1) {
                    $nilaiPoin += $poinSoal;
                }
            }
            /*
            |--------------------------------------------------------------------------
            | PG → pakai opsi_jawaban.poin
            |--------------------------------------------------------------------------
            */
            if ($bankSoal->tipe === 'pg') {
                if (isset($jawabanUser[0])) {
                    $opsi = OpsiJawaban::where('soal_id', $bankSoal->id)
                        ->where('label', $jawabanUser[0])
                        ->first();

                    if ($opsi && $opsi->is_correct) {
                        $nilaiPoin += (float) ($opsi->poin ?? 0);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PG KOMPLEKS → aturan nasional
            |--------------------------------------------------------------------------
            */
            if ($bankSoal->tipe === 'pg_kompleks') {

                $pernyataan = BanksoalPernyataan::where('banksoal_id', $bankSoal->id)
                    ->orderBy('urutan')
                    ->get();

                $jumlahBenar = 0;

                foreach ($pernyataan as $p) {
                    if (
                        isset($jawabanUser[((int) $p->urutan) - 1]) &&
                        (int) $jawabanUser[((int) $p->urutan) - 1] === (int) $p->jawaban_benar
                    ) {
                        $jumlahBenar++;
                    }
                }

                if ($jumlahBenar === 4) {
                    $nilaiPoin += 1.0;
                } elseif ($jumlahBenar === 3) {
                    $nilaiPoin += 0.6;
                } elseif ($jumlahBenar === 2) {
                    $nilaiPoin += 0.2;
                }
            }

            $navigasi[] = [
                'nomor'  => $index + 1,
                'status' => $status,
            ];
        }

        return response()->json([
            'paket'        => $attempt->tryout->paket,
            'durasi_menit' => $attempt->tryout->durasi_menit,
            'jumlah_soal'  => $jawaban->count(),
            'benar'        => $benar,
            'salah'        => $salah,
            'kosong'       => $kosong,
            'navigasi'     => $navigasi,
            'nilai_poin'   => round($nilaiPoin, 1)
        ]);
    }

    public function finish($id)
    {
        return 'oke';

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
        $this->hitungNilai($attempt);

        return response()->json([
            'message' => 'Tryout berhasil diakhiri'
        ]);
    }

    private function hitungNilai($attempt)
    {
        $totalPoin = 0;

        $jawabanPeserta = JawabanPeserta::where('attempt_id', $attempt->id)->get();

        foreach ($jawabanPeserta as $jawaban) {

            $bankSoal = BankSoal::find($jawaban->banksoal_id);
            if (! $bankSoal) continue;

            // ambil tryout_soal untuk poin
            $tryoutSoal = TryoutSoal::where('tryout_id', $attempt->tryout_id)
                ->where('banksoal_id', $bankSoal->id)
                ->first();

            $poinSoal = (float) ($tryoutSoal->poin ?? 0);

            $jawabanUser = json_decode($jawaban->jawaban, true) ?? [];

            /*
            |--------------------------------------------------------------------------
            | ISIAN → pakai tryout_soal.poin
            |--------------------------------------------------------------------------
            */
            if ($bankSoal->tipe === 'isian') {
                if (
                    isset($bankSoal->jawaban) &&
                    isset($jawabanUser[0]) &&
                    strtolower(trim($jawabanUser[0])) === strtolower(trim($bankSoal->jawaban))
                ) {
                    $totalPoin += $poinSoal;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PILIHAN GANDA BIASA → pakai opsi_jawaban.poin
            |--------------------------------------------------------------------------
            */
            if ($bankSoal->tipe === 'pg') {
                if (isset($jawabanUser[0])) {
                    $opsi = OpsiJawaban::where('soal_id', $bankSoal->id)
                        ->where('label', $jawabanUser[0])
                        ->first();

                    if ($opsi && $opsi->is_correct) {
                        $totalPoin += (float) ($opsi->poin ?? 0);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PILIHAN GANDA KOMPLEKS (aturan nasional)
            |--------------------------------------------------------------------------
            */
            if ($bankSoal->tipe === 'pg_kompleks') {

                $pernyataan = BanksoalPernyataan::where('banksoal_id', $bankSoal->id)
                    ->orderBy('urutan')
                    ->get();

                $jumlahBenar = 0;

                foreach ($pernyataan as $p) {
                    if (
                        isset($jawabanUser[$p->urutan]) &&
                        (int) $jawabanUser[$p->urutan] === (int) $p->jawaban_benar
                    ) {
                        $jumlahBenar++;
                    }
                }

                if ($jumlahBenar === 4) {
                    $totalPoin += 1.0;
                } elseif ($jumlahBenar === 3) {
                    $totalPoin += 0.6;
                } elseif ($jumlahBenar === 2) {
                    $totalPoin += 0.2;
                }
            }
        }

        $attempt->update([
            'nilai' => $totalPoin
        ]);
    }
}
