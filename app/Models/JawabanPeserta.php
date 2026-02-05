<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanPeserta extends Model
{
    protected $table = 'jawaban_peserta';

    protected $fillable = [
        'attempt_id',
        'banksoal_id',
        'jawaban',
    ];

    /**
     * Relasi ke attempt (satu attempt punya banyak jawaban)
     */
    public function attempt()
    {
        return $this->belongsTo(Attempt::class, 'attempt_id');
    }

    /**
     * Relasi ke bank soal
     */
    public function banksoal()
    {
        return $this->belongsTo(BankSoal::class, 'banksoal_id');
    }
}