<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;

class TryoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Tryout::query()
            ->with(['komponen', 'pembuat'])
            ->withCount(['questions as total_soal']);

        if ($request->filled('komponen_id')) {
            $query->whereHas('komponen', function ($q) use ($request) {
                $q->where('komponen.id', $request->komponen_id);
            });
        }

        $data = $query->latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'paket' => $item->paket,
                'komponen_id' => $item->komponen->pluck('id')->first(), // Optional fallback
                'komponen' => $item->komponen->isNotEmpty() ? $item->komponen->pluck('nama_komponen')->implode(', ') : '-',
                'total_soal' => $item->total_soal ?? 0,
                'status' => $item->status,
                'show_pembahasan' => (bool) $item->show_pembahasan,
                'pembuat' => $item->pembuat?->name ?? '-',
                'mulai' => $item->mulai,
                'selesai' => $item->selesai,
                'created_at' => optional($item->created_at)->format('Y-m-d'),
                'requires_access_key' => filled(trim((string) $item->access_key)),
            ];
        });

        return response()->json($data);
    }

    public function show($id)
    {
        $tryout = Tryout::with('komponen')->findOrFail($id);

        return response()->json([
            'id' => $tryout->id,
            'paket' => $tryout->paket,
            'komponen' => $tryout->komponen->map(function ($k) {
                return [
                    'id' => $k->id,
                    'nama_komponen' => $k->nama_komponen,
                    'mata_uji' => $k->mata_uji,
                    'urutan' => optional($k->pivot)->urutan,
                    'durasi_menit' => optional($k->pivot)->durasi_menit,
                ];
            }),
            'komponen_id' => $tryout->komponen->pluck('id')->first(),
            'komponen_nama' => $tryout->komponen->isNotEmpty() ? $tryout->komponen->pluck('nama_komponen')->implode(', ') : '-',
            'tingkat' => $tryout->komponen->first()?->mata_uji ?? '-',
            'durasi_menit' => $tryout->durasi_menit,
            'mulai' => $tryout->mulai,
            'selesai' => $tryout->selesai,
            'status' => $tryout->status,
            'ketentuan_khusus' => $tryout->ketentuan_khusus,
            'pesan_selesai' => $tryout->pesan_selesai,
            'access_key' => $tryout->access_key,
            'access_key_info' => $tryout->access_key_info,
            'show_pembahasan' => (bool) $tryout->show_pembahasan,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paket' => 'required|string|max:255',
            'komponen' => 'required|array',
            'komponen.*.id' => 'required|integer|exists:komponen,id',
            'komponen.*.durasi_menit' => 'required|integer|min:1',
            'durasi_menit' => 'required|integer|min:1',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'status' => 'nullable|in:draft,active,finished',
            'access_key' => 'nullable|string|max:255',
            'access_key_info' => 'nullable|string|max:500',
            'ketentuan_khusus' => 'nullable|string',
            'pesan_selesai' => 'nullable|string',
        ]);

        $tryout = Tryout::create([
            'paket' => $data['paket'],
            'durasi_menit' => $data['durasi_menit'],
            'mulai' => $data['mulai'],
            'selesai' => $data['selesai'],
            'status' => $data['status'] ?? 'draft',
            'created_by' => $request->user()?->id,
            'access_key' => $data['access_key'] ?? null,
            'access_key_info' => $data['access_key_info'] ?? null,
            'ketentuan_khusus' => $data['ketentuan_khusus'] ?? null,
            'pesan_selesai' => $data['pesan_selesai'] ?? null,
        ]);

        $komponenSync = [];
        foreach ($data['komponen'] as $index => $k) {
            $komponenSync[$k['id']] = [
                'urutan' => $index + 1,
                'durasi_menit' => $k['durasi_menit']
            ];
        }
        $tryout->komponen()->sync($komponenSync);

        return response()->json([
            'success' => true,
            'message' => 'Tryout berhasil dibuat',
            'data' => $tryout,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'paket' => 'required|string|max:255',
            'komponen' => 'required|array',
            'komponen.*.id' => 'required|integer|exists:komponen,id',
            'komponen.*.durasi_menit' => 'required|integer|min:1',
            'durasi_menit' => 'required|integer|min:1',
            'mulai' => 'required|date',
            'selesai' => 'required|date|after_or_equal:mulai',
            'status' => 'required|in:draft,active,finished',
            'access_key' => 'nullable|string|max:255',
            'access_key_info' => 'nullable|string|max:500',
            'ketentuan_khusus' => 'nullable|string',
            'pesan_selesai' => 'nullable|string',
        ]);

        $tryout = Tryout::findOrFail($id);
        $tryout->update([
            'paket' => $data['paket'],
            'durasi_menit' => $data['durasi_menit'],
            'mulai' => $data['mulai'],
            'selesai' => $data['selesai'],
            'status' => $data['status'],
            'access_key' => $data['access_key'] ?? null,
            'access_key_info' => $data['access_key_info'] ?? null,
            'created_by' => $request->user()?->id,
            'ketentuan_khusus' => $data['ketentuan_khusus'] ?? null,
            'pesan_selesai' => $data['pesan_selesai'] ?? null,
        ]);

        $komponenSync = [];
        foreach ($data['komponen'] as $index => $k) {
            $komponenSync[$k['id']] = [
                'urutan' => $index + 1,
                'durasi_menit' => $k['durasi_menit']
            ];
        }
        $tryout->komponen()->sync($komponenSync);

        return response()->json([
            'success' => true,
            'message' => 'Tryout berhasil diperbarui',
            'data' => $tryout,
        ]);
    }
}
