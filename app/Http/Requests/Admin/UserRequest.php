<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isCreate = $this->isMethod('POST');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['required', 'string', Rule::in(['admin', 'staff', 'customer'])],
            'is_active' => ['nullable', 'boolean'],
            'password' => [
                $isCreate ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    /**
     * Siapkan input sebelum validasi dijalankan (normalisasi tipe data).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        } else {
            $this->merge([
                'is_active' => false,
            ]);
        }
    }

    /**
     * Nama atribut khusus untuk pesan validasi ramah pengguna.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'email' => 'Alamat Email',
            'role' => 'Peran Pengguna',
            'is_active' => 'Status Akun',
            'password' => 'Kata Sandi',
        ];
    }

    /**
     * Pesan validasi kustom dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Alamat email ini sudah terdaftar oleh pengguna lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus terdiri dari :min karakter.',
        ];
    }
}
