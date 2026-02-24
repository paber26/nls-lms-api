<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankSoal;
use App\Models\OpsiJawaban;
use App\Models\BankSoalPernyataan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankSoalController extends Controller
{
    // 🔹 GET /api/bank-soal
    public function index(Request $request)
    {
        $query = BankSoal::with(['mapel', 'pembuat']);

        // 🔍 Filter by mapel (by name)
        if ($request->filled('mapel')) {
            $query->whereHas('mapel', function ($q) use ($request) {
                $q->where('nama', $request->mapel);
            });
        }

        // 🔍 Filter by status (if column exists)php artisan tinker
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query
            ->latest()
            ->get()
            ->map(function ($item) {
                $jumlahTerpakai = DB::table('tryout_soal')
                    ->where('banksoal_id', $item->id)
                    ->count();

                return [
                    'id' => $item->id,
                    'pertanyaan' => $item->pertanyaan,
                    'mapel' => $item->mapel->nama ?? '-',
                    'pembuat' => $item->pembuat->name ?? '-',
                    'jumlah_terpakai' => $jumlahTerpakai,
                ];
            });

        return response()->json($data);
    }

    /**
     * 🔹 GET /api/banksoal/tryout
     * Digunakan khusus untuk pemilihan soal ke tryout
     * (ringan & tanpa perhitungan tambahan)
     */
    public function listForTryout(Request $request)
    {
        $query = BankSoal::query()
            ->with('mapel:id,nama');

        // 🔍 filter keyword soal
        if ($request->filled('q')) {
            $query->where('pertanyaan', 'like', '%' . $request->q . '%');
        }

        // 🔍 filter mapel
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        return response()->json(
            $query->latest()->get()->map(function ($soal) {
                return [
                    'id'          => $soal->id,
                    'pertanyaan'  => $soal->pertanyaan,
                    'mapel_id'    => $soal->mapel_id,
                    'mapel_nama'  => $soal->mapel?->nama,
                ];
            })
        );
    }


    // 🔹 POST /api/bank-soal
    public function store(Request $request)
    {
        DB::beginTransaction();


        try {
            // 1️⃣ Simpan soal utama
            $soal = BankSoal::create([
                'mapel_id'   => $request->mapel_id,
                'tipe'       => $request->tipe,
                'pertanyaan' => $request->pertanyaan,
                'pembahasan' => $request->pembahasan,
                'jawaban'    => $request->tipe === 'isian'
                    ? $request->jawaban_isian
                    : null,
                // 'created_by' => auth()->id() ?? 1,
                'created_by' => $request->user()?->id ?? 1,
                // 'created_by' => 1,
            ]);


            // 2️⃣ Jika PG → simpan opsi
            if ($request->tipe === 'pg') {
                $idOpsiBenar = null;

                foreach ($request->opsi_jawaban as $index => $opsi) {
                    $row = OpsiJawaban::create([
                        'soal_id'    => $soal->id,
                        'label'      => chr(65 + $index), // A, B, C, D, ...
                        'teks'       => $opsi['text'],
                        'poin'       => $opsi['poin'] ?? 0,
                        'is_correct' => $opsi['is_correct'] ?? false,
                    ]);

                    if (!empty($opsi['is_correct'])) {
                        $idOpsiBenar = $row->id;
                    }
                }

                // 3️⃣ Update idopsijawaban di banksoal
                $soal->update([
                    'idopsijawaban' => $idOpsiBenar
                ]);
            }
            

            // 2️⃣ Jika PG Kompleks → simpan pernyataan benar/salah
            if ($request->tipe === 'pg_kompleks') {
                foreach ($request->pernyataan as $index => $item) {
                    $cek = BankSoalPernyataan::create([
                        'banksoal_id'   => $soal->id,
                        'urutan'        => $index + 1,
                        'teks'          => $item['text'],
                        'jawaban_benar' => $item['jawaban'],
                    ]);
                    // return $cek;
                }

            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil disimpan'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🔹 GET /api/bank-soal/{id}
    public function show($id)
    {
        $bankSoal = BankSoal::with(['mapel', 'opsiJawaban', 'pernyataanKompleks'])
            ->findOrFail($id);

        return response()->json([
            'id' => $bankSoal->id, 
            'mapel_id' => $bankSoal->mapel_id,
            'tipe' => $bankSoal->tipe,
            'pertanyaan' => $bankSoal->pertanyaan,
            'pembahasan' => $bankSoal->pembahasan,
            'jawaban' => $bankSoal->jawaban,
            'opsi_jawaban' => $bankSoal->opsiJawaban->map(function ($o) {
                return [
                    'text' => $o->teks,
                    'poin' => $o->poin,
                    'is_correct' => (bool) $o->is_correct,
                ];
            }),
            'pernyataan' => $bankSoal->pernyataanKompleks?->map(function ($p) {
                return [
                    'text' => $p->teks,
                    'jawaban' => (bool) $p->jawaban_benar,
                ];
            }),
        ]);
    }

    // 🔹 PUT /api/bank-soal/{id}
    public function update(Request $request, $id)
    {
        $bankSoal = BankSoal::findOrFail($id);

        $request->validate([
            'mapel_id' => 'required|exists:mapel,id',
            'tipe' => 'required|in:pg,isian,pg_kompleks',
            'pertanyaan' => 'required|string',
            'pembahasan' => 'nullable|string',
            'jawaban_isian' => 'nullable|string',
            'opsi_jawaban' => 'array',
        ]);

        DB::beginTransaction();

        try {
            $bankSoal = BankSoal::findOrFail($id);

            // update soal utama
            $bankSoal->update([
                'mapel_id' => $request->mapel_id,
                'tipe' => $request->tipe,
                'pertanyaan' => $request->pertanyaan,
                'pembahasan' => $request->pembahasan,
                'jawaban' => $request->tipe === 'isian'
                    ? $request->jawaban_isian
                    : null,
                'idopsijawaban' => null,
            ]);

            // hapus opsi lama jika PG
            if ($request->tipe === 'pg') {
                OpsiJawaban::where('soal_id', $bankSoal->id)->delete();

                $idOpsiBenar = null;

                foreach ($request->opsi_jawaban as $index => $opsi) {
                    $row = OpsiJawaban::create([
                        'soal_id' => $bankSoal->id,
                        'label' => chr(65 + $index),
                        'teks' => $opsi['text'],
                        'poin' => $opsi['poin'] ?? 0,
                        'is_correct' => $opsi['is_correct'] ?? false,
                    ]);

                    if (!empty($opsi['is_correct'])) {
                        $idOpsiBenar = $row->id;
                    }
                }

                $bankSoal->update([
                    'idopsijawaban' => $idOpsiBenar
                ]);
            }

            // hapus pernyataan lama jika PG Kompleks
            if ($request->tipe === 'pg_kompleks') {
                $bankSoal->pernyataanKompleks()->delete();

                foreach ($request->pernyataan as $index => $item) {
                    BankSoalPernyataan::create([
                        'banksoal_id'   => $bankSoal->id,
                        'urutan'     
                           => $index + 1,
                        'teks'          => $item['text'],
                        'jawaban_benar' => $item['jawaban'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil diperbarui'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🔹 DELETE /api/bank-soal/{id}
    public function destroy($id)
    {
        $bankSoal = BankSoal::findOrFail($id);
        $bankSoal->delete();

        return response()->json([
            'message' => 'Bank soal berhasil dihapus'
        ]);
    }
}