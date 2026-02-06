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
        'is_correct',
    ];

    protected $casts = [
        'jawaban' => 'array',
        'is_correct' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(Attempt::class, 'attempt_id');
    }

    public function banksoal()
    {
        return $this->belongsTo(BankSoal::class, 'banksoal_id');
    }
}