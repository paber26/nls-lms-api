<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    protected $table = 'modul';

    protected $fillable = [
        'kursus_id',
        'nama',
        'status',
        'urutan',
    ];

    public function kursus()
    {
        return $this->belongsTo(Kursus::class, 'kursus_id');
    }

    public function materi()
    {
        return $this->hasMany(Materi::class, 'modul_id')->orderBy('urutan');
    }
}
