<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseLearningOutcomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                // HANYA GUNAKAN SATU ATURAN UNIQUE YANG BENAR
                Rule::unique('course_learning_outcomes')->ignore($this->cpmk),
            ],
            'description'    => ['required', 'string'],
            'mk_ids'         => ['required', 'array', 'min:1'],
            'mk_ids.*.value' => ['required', 'exists:courses,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'Kode CPMK',
            'description' => 'Deskripsi',
            'mk_ids' => 'Mata Kuliah',
            'mk_ids.*' => 'Mata Kuliah Pilihan',
        ];
    }
}
