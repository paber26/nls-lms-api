<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptKomponen extends Model
{
    protected $table = 'attempt_komponen';
    
    protected $fillable = [
        'attempt_id',
        'komponen_id',
        'mulai',
        'selesai',
        'status',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(Attempt::class, 'attempt_id');
    }

    public function komponen()
    {
        return $this->belongsTo(Komponen::class, 'komponen_id');
    }
}
