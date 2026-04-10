<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpProblem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function testCases()
    {
        return $this->hasMany(CpTestCase::class, 'problem_id');
    }

    public function submissions()
    {
        return $this->hasMany(CpSubmission::class, 'problem_id');
    }
}
