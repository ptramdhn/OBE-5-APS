<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CplBkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // CPL yang dipilih harus ada dan valid.
            'cpl_id' => ['required', 'exists:program_learning_outcomes,id'],

            // Harus ada minimal satu Bahan Kajian (BK) yang dipilih.
            'bk_ids'   => ['required', 'array', 'min:1'],
            
            // Validasi untuk setiap item di dalam array `bk_ids`.
            'bk_ids.*.value' => [
                'required', 
                'exists:study_materials,id', // Pastikan ID Bahan Kajian (BK) valid.
                'distinct'                   // Pastikan tidak ada BK yang sama dipilih dua kali.
            ],
        ];
    }

    /**
     * Atur nama atribut untuk pesan error yang lebih mudah dibaca.
     */
    public function attributes(): array
    {
        return [
            'cpl_id'           => 'Capaian Pembelajaran Lulusan (CPL)',
            'bk_ids'           => 'Bahan Kajian (BK)',
            'bk_ids.*.value' => 'Bahan Kajian Pilihan',
        ];
    }
}
