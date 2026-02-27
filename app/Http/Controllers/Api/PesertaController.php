<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PesertaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'peserta')
            ->with('sekolah');

        // SEARCH (nama/email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // FILTER SEKOLAH
        if ($request->filled('sekolah')) {
            $query->where('sekolah_nama', 'like', "%{$request->sekolah}%");
        }

        // FILTER KELAS
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        // FILTER STATUS PROFIL
        if ($request->filled('status')) {
            if ($request->status === 'Lengkap') {
                $query->whereNotNull('nama_lengkap')
                      ->whereNotNull('sekolah_id')
                      ->whereNotNull('kelas')
                      ->whereNotNull('whatsapp');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('nama_lengkap')
                      ->orWhereNull('sekolah_id')
                      ->orWhereNull('kelas')
                      ->orWhereNull('whatsapp');
                });
            }
        }

        $perPage = $request->get('per_page', 10);

        $peserta = $query
            ->select(
                'id',
                'nama_lengkap',
                'email',
                'sekolah_id',
                'sekolah_nama',
                'kelas',
                'whatsapp',
                'created_at'
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $peserta->getCollection()->transform(function ($user) {

            $profilLengkap =
                !empty($user->nama_lengkap) &&
                !empty($user->sekolah_id) &&
                !empty($user->kelas) &&
                !empty($user->whatsapp);

            return [
                'id' => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'email' => $user->email,
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