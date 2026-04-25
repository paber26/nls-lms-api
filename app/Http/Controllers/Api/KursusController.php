<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kursus;
use Illuminate\Http\Request;

class KursusController extends Controller
{
    public function index(Request $request)
    {
        $query = Kursus::withCount('moduls');
        
        // Optional filters if we need them, but frontend handles it for now.
        $kursus = $query->orderBy('id', 'desc')->get();

        // Map moduls_count into the 'materi' field for frontend compatibility
        $kursus->each(function ($item) {
            $item->materi = $item->moduls_count;
        });
        
        return response()->json([
            'success' => true,
            'data' => $kursus
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'program' => 'nullable|string|max:255',
            'harga' => 'required|integer',
            'deskripsi' => 'nullable|string',
            'status' => 'required|string'
        ]);

        $kursus = Kursus::create($request->only([
            'nama', 'program', 'harga', 'deskripsi', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kursus berhasil ditambahkan',
            'data' => $kursus
        ], 201);
    }

    public function show($id)
    {
        $kursus = Kursus::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $kursus
        ]);
    }

    public function update(Request $request, $id)
    {
        $kursus = Kursus::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'program' => 'nullable|string|max:255',
            'harga' => 'required|integer',
            'deskripsi' => 'nullable|string'
        ]);

        $kursus->update($request->only([
            'nama', 'program', 'harga', 'deskripsi'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kursus berhasil diubah',
            'data' => $kursus
        ]);
    }

    public function destroy($id)
    {
        $kursus = Kursus::findOrFail($id);
        $kursus->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kursus berhasil dihapus'
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $kursus = Kursus::findOrFail($id);
        
        $request->validate([
            'status' => 'required|string'
        ]);

        $kursus->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diubah',
            'data' => $kursus
        ]);
    }
}
