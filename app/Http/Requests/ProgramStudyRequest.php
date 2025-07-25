<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramStudyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
           'name' => [
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
            'code' => 'Kode Prodi',
            'name' => 'Nama Prodi',
        ];
    }
}
