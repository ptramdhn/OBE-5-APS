<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
           'name' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('user')),
            ],
            'password' => [
                'sometimes', 
                'nullable', 
                'confirmed',
                Password::defaults(),
            ],
            'role' => [
                'required',
                'exists:roles,id',
            ],
            'prodi_id' => [
                'nullable',
                'exists:program_studies,id',
            ],
        ];
    }

    public function attributes(): array
    {
        return[
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Peran',
            'prodi_id' => 'Program Studi',
        ];
    }
}
