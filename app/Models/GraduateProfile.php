<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GraduateProfile extends Model
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
        return $this->belongsToMany(ProgramLearningOutcome::class, 'cpl_pl', 'pl_id', 'cpl_id')
                    ->withTimestamps();
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
