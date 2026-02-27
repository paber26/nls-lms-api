<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ImportPeserta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:peserta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import peserta dari file peserta.csv ke tabel users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = storage_path('app/peserta.csv');

        if (!file_exists($file)) {
            $this->error("File tidak ditemukan di: {$file}");
            return Command::FAILURE;
        }

        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Gagal membuka file CSV.");
            return Command::FAILURE;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            $this->error("Header CSV tidak terbaca.");
            return Command::FAILURE;
        }

        $lineNumber = 1;
        $success = 0;
        $this->info("Memulai proses import...");

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count($row) !== count($header)) {
                $this->error("Jumlah kolom tidak sesuai pada baris ke-{$lineNumber}");
                fclose($handle);
                return Command::FAILURE;
            }

            $data = array_combine($header, $row);

            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'nama_lengkap' => $data['nama_lengkap'],
                    'sekolah_nama' => $data['sekolah_nama'],
                    'kelas' => $data['kelas'] == 11 ? 'XI' : 'X',
                    'whatsapp' => $data['whatsapp'],
                    'minat' => json_decode($data['minat'], true),
                    'password' => Hash::make($data['whatsapp']),
                    'role' => 'peserta',
                    'is_active' => 1,
                    'is_event_registered' => 1,
                ]
            );

            $success++;

            // Tampilkan progress setiap 100 baris
            if ($lineNumber % 50 === 0) {
                $this->line("Sedang memproses baris ke-{$lineNumber}...");
            }
        }

        fclose($handle);
        $this->info("Import selesai. Total berhasil: {$success}");
    }
}
