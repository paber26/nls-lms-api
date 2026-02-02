<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tryout;
use App\Models\BankSoal;
use App\Models\TryoutSoal;
use Illuminate\Support\Facades\DB;

class TryoutSoalController extends Controller
{
    /**
     * GET /api/tryout/{id}/soal
     * Ambil semua soal dalam tryout (berdasarkan urutan)
     */
    public function index($id)
    {
        $soal = TryoutSoal::with('banksoal')
            ->where('tryout_id', $id)
            ->orderBy('urutan')
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->banksoal->id,
                    'pertanyaan' => $item->banksoal->pertanyaan,
                    'urutan'     => $item->urutan,
                ];
            });

        return response()->json($soal);
    }

    /**
     * GET /api/tryout/{id}/soal-detail
     * Ambil soal tryout lengkap (termasuk jawaban & pembahasan)
     * Aman: tidak mengganggu endpoint lama
     */
    public function indexDetail($id)
    {
        $soal = TryoutSoal::with('banksoal.opsiJawaban')
            ->where('tryout_id', $id)
            ->orderBy('urutan')
            ->get()
            ->map(function ($item) {
                $opsi = $item->banksoal->opsiJawaban ?? collect();

                $jawabanLabel = $opsi
                    ->where('is_correct', 1)
                    ->first()
                    ?->label;

                return [
                    'id'            => $item->banksoal->id,
                    'pertanyaan'    => $item->banksoal->pertanyaan,
                    'tipe'          => $item->banksoal->tipe,
                    'jawaban'       => $item->banksoal->jawaban,
                    'jawaban_label' => $jawabanLabel,
                    'pembahasan'    => $item->banksoal->pembahasan,
                    'urutan'        => $item->urutan,
                    'opsi' => $opsi->map(function ($o) {
                        return [
                            'id'         => $o->id,
                            'label'      => $o->label,
                            'teks'       => $o->teks,
                            'poin'       => $o->poin,
                            'is_correct' => (bool) $o->is_correct,
                        ];
                    })->values(),
                ];
            });

        return response()->json($soal);
    }

    /**
     * POST /api/tryout/{id}/soal
     * Tambahkan soal ke tryout (urutan otomatis di akhir)
     */
    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'banksoal_id' => 'required|exists:banksoal,id'
        ]);

        // Cegah duplikasi soal dalam satu tryout
        $exists = TryoutSoal::where('tryout_id', $id)
            ->where('banksoal_id', $data['banksoal_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Soal sudah ada di tryout ini'
            ], 409);
        }

        // Tentukan urutan terakhir
        $lastOrder = TryoutSoal::where('tryout_id', $id)
            ->max('urutan');

        TryoutSoal::create([
            'tryout_id'   => $id,
            'banksoal_id' => $data['banksoal_id'],
            'urutan'      => ($lastOrder ?? 0) + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil ditambahkan'
        ]);
    }

    /**
     * DELETE /api/tryout/{id}/soal/{banksoalId}
     * Hapus soal dari tryout (TIDAK menghapus bank soal)
     */
    public function destroy($id, $banksoalId)
    {
        TryoutSoal::where('tryout_id', $id)
            ->where('banksoal_id', $banksoalId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil dihapus dari tryout'
        ]);
    }

    /**
     * PUT /api/tryout/{id}/soal/urutan
     * Simpan ulang urutan soal (bulk update)
     */
    public function updateUrutan(Request $request, $id)
    {
        $data = $request->validate([
            '*.banksoal_id' => 'required|integer',
            '*.urutan'      => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($data, $id) {
            foreach ($data as $item) {
                TryoutSoal::where('tryout_id', $id)
                    ->where('banksoal_id', $item['banksoal_id'])
                    ->update([
                        'urutan'     => $item['urutan'],
                        'updated_at'=> now()
                    ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Urutan soal berhasil disimpan'
        ]);
    }

    
}