<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Ambil semua user
     */
    public function index()
    {
        // return Auth::user()->role !== 'admin';

        // Optional: hanya admin yang boleh akses
        if (Auth::user()->role !== 'admin') {
            return response()->json("tidak dikenali");
        }

        $users = User::select('id', 'name', 'email', 'role')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($users);
    }

    /**
     * Update role user
     */
    public function updateRole(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::findOrFail($id);

        // Jangan biarkan admin mengubah dirinya sendiri jadi peserta
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return response()->json([
                'message' => 'Tidak bisa menurunkan role diri sendiri'
            ], 400);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'Role berhasil diperbarui'
        ]);
    }
}