<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSoalPernyataan extends Model
{
    use HasFactory;

    protected $table = 'banksoal_pernyataan';
    
    protected $fillable = [
        'banksoal_id',
        'urutan',
        'teks',
        'jawaban_benar'
    ];

    protected $casts = [
        'jawaban_benar' => 'boolean',
    ];

    public function bankSoal()
    {
        return $this->belongsTo(BankSoal::class, 'banksoal_id');
    }

}
