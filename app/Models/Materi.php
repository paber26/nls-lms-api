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
        'videoUrl',
        'deskripsi',
        'durasi',
        'urutan',
    ];

    public function modul()
    {
        return $this->belongsTo(Modul::class, 'modul_id');
    }
}
