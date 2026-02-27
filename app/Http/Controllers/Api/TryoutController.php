<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;

class TryoutController extends Controller
{
    // 🔹 GET /api/tryout
    public function index()
    {
        $data = Tryout::with(['mapel', 'pembuat'])
            ->withCount(['questions as total_soal'])
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'paket' => $item->paket,
                    'mapel' => $item->mapel->nama ?? '-',
                    'total_soal' => $item->total_soal ?? 0,
                    'status' => $item->status,
                    'pembuat' => $item->pembuat->name ?? '-',
                    'mulai' => $item->mulai,
                    'selesai' => $item->selesai,
                    'created_at' => $item->created_at->format('Y-m-d'),
                ];
            });

        return response()->json($data);
    }

    // 🔹 GET /api/tryout/{id}
    public function show($id)
    {
        $tryout = Tryout::with('mapel')->findOrFail($id);

        return response()->json([
            'id'            => $tryout->id,
            'paket'         => $tryout->paket,
            'mapel_id'      => $tryout->mapel_id,
            'mapel_nama'    => $tryout->mapel?->nama,
            'tingkat'       => $tryout->mapel?->tingkat,
            'durasi_menit'  => $tryout->durasi_menit,
            'mulai'         => $tryout->mulai,
            'selesai'       => $tryout->selesai,
            'status'        => $tryout->status,
            'ketentuan_khusus' => $tryout->ketentuan_khusus,
            'pesan_selesai' => $tryout->pesan_selesai,
        ]);
    }

    // 🔹 POST /api/tryout
    public function store(Request $request)
    {
        $data = $request->validate([
            'paket'        => 'required|string|max:255',
            'mapel_id'     => 'required|integer',
            'durasi_menit' => 'required|integer',
            'mulai'        => 'required|date',
            'selesai'      => 'required|date',
            // 'status'       => 'required|in:draft,active,finished',
        ]);
            
        $tryout = Tryout::create([
            'paket'        => $data['paket'],
            'mapel_id'     => $data['mapel_id'],
            'durasi_menit' => $data['durasi_menit'],
            'mulai'        => $data['mulai'],
            'selesai'      => $data['selesai'],
            // 'status'       => $data['status'],
            'created_by'   => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tryout berhasil dibuat',
            'data'    => $tryout
        ], 201);
    }

    // 🔹 PUT /api/tryout/{id}
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'paket'        => 'required|string|max:255',
            'mapel_id'     => 'required|integer',
            'durasi_menit' => 'required|integer',
            'mulai'        => 'required|date',
            'selesai'      => 'required|date',
            'status'       => 'required|in:draft,active,finished',
            'ketentuan_khusus' => 'nullable|string',
            'pesan_selesai' => 'nullable|string'
        ]);

        $tryout = Tryout::findOrFail($id);

        $tryout->update([
            'paket'        => $data['paket'],
            'mapel_id'     => $data['mapel_id'],
            'durasi_menit' => $data['durasi_menit'],
            'mulai'        => $data['mulai'],
            'selesai'      => $data['selesai'],
            'status'       => $data['status'],
            'created_by'   => $request->user()?->id,
            'ketentuan_khusus' => $data['ketentuan_khusus'],
            'pesan_selesai' => $data['pesan_selesai'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tryout berhasil diperbarui',
            'data'    => $tryout
        ]);
    }
}