<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'prodi' => $this->whenLoaded('programStudy', function () {
                return [
                    'id' => $this->programStudy->id,
                    'name' => $this->programStudy->name,
                ];
            }),
            'programLearningOutcomes' => $this->whenLoaded('programLearningOutcomes'),
            'study_materials' => $this->whenLoaded('studyMaterials'),
            'id_mk' => $this->id_mk,
            'kode_mk' => $this->kode_mk,
            'name' => $this->name,
            'semester' => $this->semester,
            'sks' => $this->sks,
            'jenis_mk' => $this->jenis_mk,
            'kelompok_mk' => $this->kelompok_mk,
            'lingkup_kelas' => $this->lingkup_kelas,
            'mode_kuliah' => $this->mode_kuliah,
            'metode_pembelajaran' => $this->metode_pembelajaran,
            'created_at' => $this->created_at,
        ];
    }
}
