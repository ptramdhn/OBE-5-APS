<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasUuids;

    protected $fillable = [
        'prodi_id',
        'id_mk',
        'kode_mk',
        'name',
        'semester',
        'sks',
        'jenis_mk',
        'kelompok_mk',
        'lingkup_kelas',
        'mode_kuliah',
        'metode_pembelajaran',
    ];

    public function programStudy(): BelongsTo
    {
        return $this->belongsTo(ProgramStudy::class, 'prodi_id');
    }

    public function studyMaterials(): BelongsToMany
    {
        return $this->belongsToMany(StudyMaterial::class, 'bk_mk', 'mk_id', 'bk_id');
    }

    public function programLearningOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(ProgramLearningOutcome::class, 'cpl_mk', 'mk_id', 'cpl_id');
    }

    public function courseLearningOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(CourseLearningOutcome::class, 'cpmk_mk', 'mk_id', 'cpmk_id')
                    ->withTimestamps();
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function($query, $search){
            $query->whereAny([
                'prodi_id',
                'id_mk',
                'kode_mk',
                'name',
                'semester',
                'sks',
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
