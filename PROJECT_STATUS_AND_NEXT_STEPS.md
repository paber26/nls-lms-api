# NLS OSN API - Project Status & Next Steps

Tanggal update: 2026-04-09

## Yang Sudah Dikerjakan
- Integrasi admin Codeforces sudah aktif:
  - Health check, lookup handle, submissions, resolve problem URL.
  - CRUD `cf-problems`.
- Fitur paket tryout CP admin sudah tersedia:
  - Endpoint `cp-tryout-packages` (list, create, detail, update, sync problems, leaderboard).
  - Skema tabel: `cp_tryout_packages` dan `cp_tryout_package_problems`.
- Endpoint user CP ditambah untuk konsumsi paket:
  - `GET /api/user/cp/packages`
  - `GET /api/user/cp/packages/{packageId}/problems`
- Filter paket user sudah disesuaikan:
  - Paket tampil berdasarkan `status=active` (tanpa blokir waktu mulai/selesai).

## Next Steps
- Finalisasi rule visibilitas paket untuk user:
  - Putuskan apakah perlu kembali ke rule berbasis waktu (`mulai/selesai`) atau tetap `active` saja.
- Tambah validasi domain bisnis pada update paket:
  - Cegah `selesai < mulai`.
  - Cegah `durasi_menit <= 0`.
- Tambah endpoint khusus edit sebagian field (opsional):
  - Misalnya `PATCH /cp-tryout-packages/{id}/schedule-status`.
- Tambah test otomatis:
  - Feature test untuk endpoint `user/cp/packages`.
  - Feature test untuk leaderboard paket CP.
- Rapikan migrasi historis:
  - Sinkronisasi tabel `migrations` agar `php artisan migrate` full tidak mencoba ulang migrasi awal.

