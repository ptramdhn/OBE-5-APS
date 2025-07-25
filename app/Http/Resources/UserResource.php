<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->whenLoaded('role', function () {
                return [
                    'id' => $this->role->id,
                    'name' => $this->role->name,
                ];
            }),
            'prodi' => $this->whenLoaded('programStudy', function () {
                return [
                    'id' => $this->programStudy->id,
                    'name' => $this->programStudy->name,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
