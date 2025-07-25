<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GraduateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
           'prodi_id' => [
                'required',
                'exists:program_studies,id',
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('graduate_profiles')->ignore($this->route('graduateProfile')),
            ],
            'description' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
        ];
    }

    public function attributes(): array
    {
        return[
            'prodi_id' => 'Program Studi',
            'code' => 'Kode PL',
            'description' => 'Deskripsi',
        ];
    }
}
