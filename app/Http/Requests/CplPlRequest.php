<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CplPlRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Set ke true, asumsikan semua user yang login bisa mengakses.
        // Anda bisa menambahkan logika otorisasi di sini nanti.
        return true;
    }

    public function rules(): array
    {
        return [
            'cpl_id' => ['required', 'exists:program_learning_outcomes,id'],
            'pl_ids'   => ['required', 'array', 'min:1'],
            
            // Validasi setiap item di dalam array `pl_ids`.
            // Tanda * adalah wildcard. Kita validasi 'value' dari setiap object.
            'pl_ids.*.value' => [
                'required', 
                'exists:graduate_profiles,id',
                'distinct'       // Pastikan tidak ada PL yang sama dipilih dua kali
            ],
        ];
    }

    /**
     * Atur nama atribut untuk pesan error yang lebih ramah.
     */
    public function attributes(): array
    {
        return [
            'cpl_id' => 'Capaian Pembelajaran Lulusan (CPL)',
            'pl_ids.*.value' => 'Profil Lulusan (PL)',
        ];
    }
}
