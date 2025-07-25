<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudyMaterialResource extends JsonResource
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
            'courses'     => $this->whenLoaded('courses'),
            'code' => $this->code,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
