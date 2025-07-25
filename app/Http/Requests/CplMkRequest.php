<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CplMkRequest extends FormRequest
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
            'mk_id' => ['required', 'exists:courses,id'],

            // Harus ada minimal satu Bahan Kajian (BK) yang dipilih.
            'cpl_ids'   => ['required', 'array', 'min:1'],
            
            // Validasi untuk setiap item di dalam array `bk_ids`.
            'cpl_ids.*.value' => [
                'required', 
                'exists:program_learning_outcomes,id', // Pastikan ID Bahan Kajian (BK) valid.
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
            'mk_id'           => 'Mata Kuliah (MK)',
            'cpl_ids'           => 'Capaian Pembelajaran Lulusan (CPL)',
            'cpl_ids.*.value' => 'Capaian Pembelajaran Lulusan Pilihan',
        ];
    }
}
