<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{
    protected $table = 'tryout';

    protected $fillable = [
        'paket',
        'komponen_id',
        'durasi_menit',
        'mulai',
        'selesai',
        'status',
        'created_by',
        'ketentuan_khusus',
        'pesan_selesai',
        'show_pembahasan',
        'access_key',
        'access_key_info',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
        'show_pembahasan' => 'boolean',
    ];

    public function komponen()
    {
        return $this->belongsToMany(Komponen::class, 'tryout_komponen', 'tryout_id', 'komponen_id')
                    ->withPivot('urutan', 'durasi_menit');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(TryoutSoal::class, 'tryout_id', 'id');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'tryout_id', 'id');
    }
}
