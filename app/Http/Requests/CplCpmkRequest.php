<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CplCpmkRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Izinkan semua pengguna yang terautentikasi untuk membuat permintaan ini.
        // Anda bisa menambahkan logika otorisasi yang lebih spesifik di sini nanti.
        return true;
    }

    public function rules(): array
    {
        return [
            // CPL yang dipilih harus ada dan valid.
            'cpl_id' => ['required', 'exists:program_learning_outcomes,id'],

            // Harus ada minimal satu CPMK yang dipilih.
            'cpmk_ids'   => ['required', 'array', 'min:1'],
            
            // Validasi untuk setiap item di dalam array `cpmk_ids`.
            'cpmk_ids.*.value' => [
                'required', 
                'exists:course_learning_outcomes,id', // Pastikan ID CPMK valid.
                'distinct'       // Pastikan tidak ada CPMK yang sama dipilih dua kali.
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
            'cpmk_ids'         => 'Capaian Pembelajaran Mata Kuliah (CPMK)',
            'cpmk_ids.*.value' => 'CPMK Pilihan',
        ];
    }
}
