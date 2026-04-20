<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use App\Models\Kursus;
use Illuminate\Http\Request;

class ModulController extends Controller
{
    public function getByKursus($kursusId)
    {
        $kursus = Kursus::findOrFail($kursusId);
        $user = auth()->user();
        
        $moduls = Modul::where('kursus_id', $kursusId)
            ->with(['materi' => function($q) use ($user) {
                $q->orderBy('urutan', 'asc');
                if ($user) {
                    $q->withExists(['usersCompleted as is_completed' => function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    }]);
                }
            }])
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'kursus' => $kursus,
            'data' => $moduls
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kursus_id' => 'required|exists:kursus,id',
            'nama' => 'required|string|max:255',
            'status' => 'required|string',
        ]);

        $modul = Modul::create([
            'kursus_id' => $request->kursus_id,
            'nama' => $request->nama,
            'status' => $request->status,
            'urutan' => Modul::where('kursus_id', $request->kursus_id)->max('urutan') + 1,
        ]);

        $modul->load('materi');

        // Increment materi/modul count ideally but we can compute dynamically on Kursus side
        
        return response()->json([
            'success' => true,
            'message' => 'Modul berhasil ditambahkan',
            'data' => $modul
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $modul = Modul::findOrFail($id);
        $modul->update([
            'nama' => $request->nama,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modul berhasil diperbarui',
            'data' => $modul
        ]);
    }

    public function destroy($id)
    {
        $modul = Modul::findOrFail($id);
        $modul->materi()->delete(); // delete children materis
        $modul->delete();

        return response()->json([
            'success' => true,
            'message' => 'Modul berhasil dihapus'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $modul = Modul::findOrFail($id);
        $request->validate([
            'status' => 'required|string'
        ]);

        $modul->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status modul berhasil diubah',
            'data' => $modul
        ]);
    }
}
