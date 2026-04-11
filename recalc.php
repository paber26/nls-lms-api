<?php

use App\Models\Attempt;
use App\Http\Controllers\Api\UserTryoutController;

$controller = new UserTryoutController();
$attempts = Attempt::whereNotNull('selesai')->orWhere('status', 'submitted')->get();
$count = 0;

echo "Memulai penghitungan ulang nilai...\n";

foreach ($attempts as $attempt) {
    $oldNilai = $attempt->nilai;
    
    // hitungNilai akan menghitung ulang dan menyimpan ke DB (update attempt)
    $controller->hitungNilai($attempt);
    
    $attempt->refresh();
    echo "Attempt ID {$attempt->id} (Tryout ID: {$attempt->tryout_id}) - Nilai sebelumnya: {$oldNilai} | Nilai baru: {$attempt->nilai}\n";
    $count++;
}

echo "Berhasil menghitung ulang nilai untuk $count attempt.\n";
