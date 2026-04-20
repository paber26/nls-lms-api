<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kursus extends Model
{
    protected $table = 'kursus';
    
    protected $fillable = [
        'nama',
        'program',
        'harga',
        'deskripsi',
        'materi',
        'siswa',
        'status',
    ];
}
