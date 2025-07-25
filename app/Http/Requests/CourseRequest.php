<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
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
            'id_mk' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses')->ignore($this->route('course')),
            ],
            'kode_mk' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses')->ignore($this->route('course')),
            ],
            'name' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'semester' => [
                'required',
                'numeric',
            ],
            'sks' => [
                'required',
                'numeric',
            ],
            'jenis_mk' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'kelompok_mk' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'lingkup_kelas' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'mode_kuliah' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'metode_pembelajaran' => [
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
            'id_mk' => 'ID',
            'kode_mk' => 'Kode',
            'name' => 'Nama',
            'semester' => 'Semester',
            'sks' => 'SKS',
            'jenis_mk' => 'Jenis MK',
            'kelompok_mk' => 'Kelompok MK',
            'lingkup_kelas' => 'Lingkup Kelas',
            'mode_kuliah' => 'Mode Kuliah',
            'metode_pembelajaran' => 'Metode Pembelajaran',
        ];
    }
}
