<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramLearningOutcomeResource extends JsonResource
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
            'code' => $this->code,
            'graduate_profiles' => $this->whenLoaded('graduateProfiles'),
            'studyMaterials' => $this->whenLoaded('studyMaterials'),
            'cpmks'       => CourseLearningOutcomeResource::collection($this->whenLoaded('cpmks')),
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
