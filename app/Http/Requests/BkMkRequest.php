<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BkMkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bk_id' => ['required', 'exists:study_materials,id'],
            'mk_ids'   => ['required', 'array', 'min:1'],
            'mk_ids.*.value' => [
                'required', 
                'exists:courses,id', // Pastikan nama tabel MK Anda adalah `courses`
                'distinct'
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'bk_id'           => 'Bahan Kajian (BK)',
            'mk_ids.*.value' => 'Mata Kuliah Pilihan',
        ];
    }
}
