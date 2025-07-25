<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ProgramStudy extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'prodi_id');
    }

    public function graduateProfiles(): HasMany
    {
        return $this->hasMany(GraduateProfile::class, 'prodi_id');
    }

    public function programLearningOutcomes(): HasMany
    {
        return $this->hasMany(ProgramLearningOutcome::class, 'prodi_id');
    }

    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class, 'prodi_id');
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function($query, $search){
            $query->whereAny([
                'code',
                'name',
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
