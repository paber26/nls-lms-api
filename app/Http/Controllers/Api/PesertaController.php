<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class PesertaController extends Controller
{
    public function index()
    {
        $peserta = User::where('role', 'peserta')
            ->with('sekolah') // pastikan ada relasi sekolah() di model User
            ->select(
                'id',
                'nama_lengkap',
                'sekolah_id',
                'sekolah_nama',
                'kelas',
                'whatsapp'
            )
            ->get()
            ->map(function ($user) {

                $profilLengkap =
                    !empty($user->nama_lengkap) &&
                    !empty($user->sekolah_id) &&
                    !empty($user->kelas) &&
                    !empty($user->whatsapp);

                return [
                    'id' => $user->id,
                    'nama_lengkap' => $user->nama_lengkap,
                    'sekolah' => optional($user->sekolah)->nama,
                    'sekolah_nama' => $user->sekolah_nama,
                    'kelas' => $user->kelas,
                    'whatsapp' => $user->whatsapp,
                    'status_profil' => $profilLengkap ? 'Lengkap' : 'Belum Lengkap',
                ];
            });

        return response()->json($peserta);
    }

    public function show($id)
    {
        $peserta = User::with([
            'sekolah',
            'attempts.tryout'
        ])->findOrFail($id);
        return $peserta;

        return response()->json([
            'id' => $peserta->id,
            'nama_lengkap' => $peserta->nama_lengkap,
            'email' => $peserta->email,
            'kelas' => $peserta->kelas,
            'whatsapp' => $peserta->whatsapp,
            'provinsi' => $peserta->provinsi,
            'kota' => $peserta->kota,
            'kecamatan' => $peserta->kecamatan,
            'role' => $peserta->role,
            'sekolah' => $peserta->sekolah ? [
                'id' => $peserta->sekolah->id,
                'nama' => $peserta->sekolah->nama,
            ] : null,
            'detail_tryout' => $peserta->attempts->map(function ($attempt) {
                return [
                    'attempt_id' => $attempt->id,
                    'tryout_id' => $attempt->tryout_id,
                    'nama_tryout' => optional($attempt->tryout)->paket,
                    'status' => $attempt->status,
                    'mulai' => $attempt->mulai,
                    'selesai' => $attempt->selesai,
                ];
            }),
        ]);
    }
}