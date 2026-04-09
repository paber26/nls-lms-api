<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCfSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cf_problem_id',
        'cf_submission_id',
        'verdict',
        'points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function problem()
    {
        return $this->belongsTo(CfProblem::class, 'cf_problem_id');
    }
}
