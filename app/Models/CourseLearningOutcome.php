<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseLearningOutcome extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'description',
    ];

    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(ProgramLearningOutcome::class, 'cpl_cpmk', 'cpmk_id', 'cpl_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'cpmk_mk', 'cpmk_id', 'mk_id')
                    ->withTimestamps();
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function($query, $search){
            $query->whereAny([
                'code',
                'description',
            ], 'REGEXP', $search);
        });
    }

    public function scopeSorting(Builder $query, array $sorts): void
    {
        $query->when($sorts['field'] ?? null && $sorts['direction'], function($query) use ($sorts){
            $query->orderBy($sorts['field'], $sorts['direction']);
        });
    }
}
