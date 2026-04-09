<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CfProblem;
use App\Models\UserCfSubmission;
use App\Services\CodeforcesService;

class UserCodeforcesController extends Controller
{
    /**
     * Tampilkan semua daftar soal Codeforces yang tersedia untuk dikerjakan.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Dapatkan semua masalah Codeforces (Opsional: difilter berdasar mapel jika perlu)
        $problems = CfProblem::with('mapel')->orderBy('cf_contest_id', 'desc')->orderBy('cf_index', 'asc')->get();
        
        // Ambil data status submission user yang sudah solved
        $solvedIds = UserCfSubmission::where('user_id', $user->id)
            ->where('verdict', 'OK')
            ->pluck('cf_problem_id')
            ->toArray();

        $problemsData = $problems->map(function ($problem) use ($solvedIds) {
            return [
                'id' => $problem->id,
                'cf_contest_id' => $problem->cf_contest_id,
                'cf_index' => $problem->cf_index,
                'name' => $problem->name,
                'mapel' => $problem->mapel ? $problem->mapel->nama : 'Informatika',
                'tags' => $problem->tags,
                'rating' => $problem->rating,
                'points' => $problem->points,
                'is_solved' => in_array($problem->id, $solvedIds),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $problemsData
        ]);
    }

    /**
     * Tampilkan detail soal beserta deskripsi HTML-nya
     */
    public function show($id, CodeforcesService $cfService)
    {
        $problem = CfProblem::with('mapel')->findOrFail($id);
        
        // Coba untuk mendapat HTML problem statement
        try {
            $statementHtml = $cfService->getProblemStatementHtml($problem->cf_contest_id, $problem->cf_index);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $problem->id,
                    'cf_contest_id' => $problem->cf_contest_id,
                    'cf_index' => $problem->cf_index,
                    'name' => $problem->name,
                    'mapel' => $problem->mapel ? $problem->mapel->nama : 'Informatika',
                    'statement_html' => $statementHtml,
                    'is_solved' => UserCfSubmission::where('user_id', request()->user()->id)
                                    ->where('cf_problem_id', $problem->id)
                                    ->where('verdict', 'OK')
                                    ->exists()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat soal dari Codeforces: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lakukan sinkronisasi ke Codeforces API untuk mengecek apakah user sudah accept soal ini
     */
    public function sync(Request $request, $id, CodeforcesService $cfService)
    {
        $user = $request->user();
        if (!$user->cf_handle) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memasukkan Username Codeforces di Profil!'
            ], 403);
        }

        $problem = CfProblem::findOrFail($id);

        try {
            $submissions = $cfService->userStatus($user->cf_handle, 1, 20); // Ambil 20 submission terbari
            
            $solved = false;
            foreach ($submissions as $sub) {
                // Periksa apakah ini kontes dan index yang tepat
                if ($sub['problem']['contestId'] == $problem->cf_contest_id && $sub['problem']['index'] == $problem->cf_index) {
                    
                    // Simpan submission rate ke DB kita (meski gak OK agar terekod history)
                    UserCfSubmission::updateOrCreate(
                        ['cf_submission_id' => $sub['id']],
                        [
                            'user_id' => $user->id,
                            'cf_problem_id' => $problem->id,
                            'verdict' => $sub['verdict'] ?? 'UNKNOWN',
                            'points' => ($sub['verdict'] === 'OK') ? $problem->points : 0,
                        ]
                    );

                    if (isset($sub['verdict']) && $sub['verdict'] === 'OK') {
                        $solved = true;
                    }
                }
            }

            if ($solved) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sukses! Solusi Anda telah diverifikasi benar oleh Codeforces.',
                    'verdict' => 'OK'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ditemukan riwayat submission dengan status Benar (Accepted) untuk soal ini.',
                    'verdict' => 'NOT_OK'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi server Codeforces: ' . $e->getMessage()
            ], 500);
        }
    }
}
