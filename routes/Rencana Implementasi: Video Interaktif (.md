Rencana Implementasi: Video Interaktif (Kuis Pop-up)
Ide menjadikan video dapat memberhentikan pemutaran secara otomatis (pause) dan memaksa siswa menjawab ujian di layar tengah sebelum mereka dapat melanjutkan adalah fitur kelas atas (Enterprise) pada LMS.

Berikut adalah rancangan cara pengaplikasiannya tanpa memerlukan perombakan fondasi yang berat!

1. Skema Penyimpanan Data (Backend)
Daripada membuat tabel database baru yang akan memperlambat kueri, kita akan menyuntikkan satu kolom baru bertipe JSON bernama kuis_interaktif pada tabel materi. Kolom tersebut akan menyimpan struktur data setiap kuis secara spesifik seperti ini:

json
[
  {
    "waktu_kemunculan": 125, // Detik (Muncul di menit ke 2:05)
    "pertanyaan": "Berdasarkan penjelasan tadi, apa ciri khas proyeksi Mercator?",
    "pilihan": ["A. Mempertahankan Jarak", "B. Mendistorsi Kutub", "C. Ekuivalen"],
    "jawaban_benar": 1 // Index yang berarti B adalah benar
  }
]
2. Pembuatan Kuis di Panel Admin (Saat Ini)
Jika Anda menekan Tambah Materi dengan tipe Video, akan muncul kotak baru bernama Pengaturan Kuis Interaktif.

Admin bisa menambah kuis sebanyak apa pun (Tekan tombol "Tambah Kuis").
Di setiap box kuis, Anda akan mengatur:
Waktu (Menit & Detik) kemunculan.
Pertanyaan Kuisnya
Kotak-kotak opsi pilihan ganda beserta tanda centang untuk menandai (set) kunci jawabannya.
3. Logika Eksekusi di Layar Siswa (Nanti, di nls-lms-user)
Sistem Video akan dicolokkan dengan YouTube Iframe API Callback.
Sistem menyalakan stopwatch rahasia dengan setInterval. Jika jarum detik YouTube menyentuh titik detik waktu_kemunculan, sistem akan otomatis:
Menjalankan fungsi .pauseVideo().
Memunculkan jendela pop-up layar penuh di atas kotak video, menutupi isi video.
Sisanya adalah sistem Gatekeeper (Penjaga Pintu): Jika siswa tidak memilih opsi jawaban_benar, muncul notifikasi merah ("Jawaban salah, tidak bisa dilanjut"). Namun, jika dijawab dengan benar: Pop-up menghilang dan video memanggil skrip .playVideo() untuk tayang kembali.
WARNING

Demi menjamin keamanan agar siswa tidak me-loncati (skip/scrubbing) garis waktu video melewati titik kuis, skrip antarmuka siswa kelak akan mendebat klik yang ditarik secara drastis (fast-forwarding restriction).