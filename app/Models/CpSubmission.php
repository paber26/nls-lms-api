<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpSubmission extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function problem()
    {
        return $this->belongsTo(CpProblem::class, 'problem_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
