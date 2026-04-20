<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'modul_id' => 'required|exists:modul,id',
            'judul' => 'required|string|max:255',
            'tipe' => 'required|string|in:article,video',
            'konten' => 'nullable|string',
            'videoUrl' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
        ]);

        $materi = Materi::create([
            'modul_id' => $request->modul_id,
            'judul' => $request->judul,
            'tipe' => $request->tipe,
            'konten' => $request->konten,
            'videoUrl' => $request->videoUrl,
            'deskripsi' => $request->deskripsi,
            'durasi' => $request->durasi,
            'urutan' => Materi::where('modul_id', $request->modul_id)->max('urutan') + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil ditambahkan',
            'data' => $materi
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $materi = Materi::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'tipe' => 'required|string|in:article,video',
            'konten' => 'nullable|string',
            'videoUrl' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
        ]);

        $materi->update($request->only([
            'judul', 'tipe', 'konten', 'videoUrl', 'deskripsi', 'durasi'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil diubah',
            'data' => $materi
        ]);
    }

    public function destroy($id)
    {
        $materi = Materi::findOrFail($id);
        $materi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil dihapus'
        ]);
    }

    public function reorder(Request $request)
    {
        // Expecting input format: materis => [{id: 1, urutan: 1}, {id: 2, urutan: 2}]
        $request->validate([
            'materis' => 'required|array',
            'materis.*.id' => 'required|exists:materi,id',
            'materis.*.urutan' => 'required|integer',
        ]);

        foreach ($request->materis as $mItem) {
            Materi::where('id', $mItem['id'])->update(['urutan' => $mItem['urutan']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan berhasil diperbarui'
        ]);
    }
}
