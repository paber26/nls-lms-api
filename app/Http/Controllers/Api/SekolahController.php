<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index(Request $request)
    {
        $query = Sekolah::query();

        // Optional: fitur search
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('npsn', 'like', '%' . $request->search . '%');
            });
        }

        $sekolah = $query
            ->withCount(['users as jumlah_peserta'])
            ->orderByDesc('jumlah_peserta')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sekolah
        ]);
    }

    public function show($id)
    {
        $sekolah = Sekolah::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $sekolah
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'npsn' => 'required|string|max:20|unique:sekolah,npsn',
            'jenjang' => 'required|string|max:50',
            'status' => 'required|in:Negeri,Swasta'
        ]);

        $sekolah = Sekolah::create([
            'nama' => $request->nama,
            'npsn' => $request->npsn,
            'jenjang' => $request->jenjang,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sekolah berhasil ditambahkan',
            'data' => $sekolah
        ], 201);
    }
}