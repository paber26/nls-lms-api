<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpTestCase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function problem()
    {
        return $this->belongsTo(CpProblem::class, 'problem_id');
    }
}
