<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapel';

    protected $fillable = [
        'kode',
        'nama',
        'tingkat',
    ];

    public $timestamps = true; // karena ada created_at

    /**
     * Relasi: 1 mapel punya banyak bank soal
     */
    public function bankSoals()
    {
        return $this->hasMany(BankSoal::class, 'mapel_id');
    }
}
