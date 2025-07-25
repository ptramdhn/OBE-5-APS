<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class StudyMaterial extends Model
{
    use HasUuids;

    protected $fillable = [
        'prodi_id',
        'code',
        'description',
    ];

    public function programStudy(): BelongsTo
    {
        return $this->belongsTo(ProgramStudy::class, 'prodi_id');
    }

    public function programLearningOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(ProgramLearningOutcome::class, 'cpl_bk', 'bk_id', 'cpl_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'bk_mk', 'bk_id', 'mk_id');
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function($query, $search){
            $query->whereAny([
                'prodi_id',
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
