<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseLearningOutcomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'description' => $this->description,
            // Sertakan relasi yang sudah di-eager load
            'prodi'       => $this->whenLoaded('programStudy'),
            'courses' => $this->whenLoaded('courses'),
        ];
    }
}
