<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'tags',
        'rating',
        'points',
    ];

    protected $casts = [
        'tags' => 'array',
        'cf_contest_id' => 'integer',
        'rating' => 'integer',
        'points' => 'integer',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
}
