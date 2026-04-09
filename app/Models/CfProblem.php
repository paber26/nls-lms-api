<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Mapel;

class CfProblem extends Model
{
    use HasUuids;

    protected $fillable = [
        'mapel_id',
        'cf_contest_id',
        'cf_index',
        'name',
        'statement_html',
        'custom_statement_html',
        'is_custom_statement',
        'tags',
        'rating',
        'points',
    ];

    protected $casts = [
        'tags' => 'array',
        'cf_contest_id' => 'integer',
        'rating' => 'integer',
        'points' => 'integer',
        'is_custom_statement' => 'boolean',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function cpTryoutPackages(): BelongsToMany
    {
        return $this->belongsToMany(
            CpTryoutPackage::class,
            'cp_tryout_package_problems',
            'cf_problem_id',
            'cp_tryout_package_id'
        )->withPivot('urutan')->withTimestamps();
    }
}
