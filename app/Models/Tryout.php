<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{
    protected $table = 'tryout';

    protected $fillable = [
        'paket',
        'mapel_id',
        'durasi_menit',
        'mulai',
        'selesai',
        'status',
        'created_by',
        'ketentuan_khusus',
        'pesan_selesai',
    ];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ✅ RELASI SOAL TRYOUT
    public function questions()
    {
        return $this->hasMany(
            TryoutSoal::class,
            'tryout_id',
            'id'
        );
    }

    // ✅ RELASI ATTEMPT USER
    public function attempts()
    {
        return $this->hasMany(
            Attempt::class,
            'tryout_id',
            'id'
        );
    }
}