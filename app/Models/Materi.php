<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materi';

    protected $fillable = [
        'modul_id',
        'judul',
        'tipe',
        'konten',
        'kunci_jawaban',
        'pembahasan',
        'kuis_interaktif',
        'videoUrl',
        'deskripsi',
        'durasi',
        'urutan',
    ];

    protected $casts = [
        'kuis_interaktif' => 'array',
    ];

    public function modul()
    {
        return $this->belongsTo(Modul::class, 'modul_id');
    }
}
