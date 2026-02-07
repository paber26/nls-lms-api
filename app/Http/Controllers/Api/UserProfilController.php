<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserProfilController extends Controller
{
    /**
     * Simpan / update profil peserta
     */
    public function store(Request $request)
    {
        $email = Auth::user()->email;

        User::where('email', $email)->update([
            'nama_lengkap' => $request->nama_lengkap,
            'sekolah_id'   => $request->sekolah_id,
            'kelas'        => $request->kelas,
            'provinsi'     => $request->provinsi,
            'kota'         => $request->kota,
            'kecamatan'   => $request->kecamatan,
            'whatsapp'     => $request->whatsapp,
            'minat'        => $request->minat,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil peserta berhasil disimpan'
        ]);
    }
}