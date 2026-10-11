<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // Honeypot fields:
            '_hp_website' => ['nullable', 'string', 'max:0'],
            '_hp_timestamp' => ['nullable', 'integer'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'phone.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            '_hp_website.max' => 'Spam terdeteksi.',
        ];
    }

    /**
     * Handle extra honeypot security verification.
     *
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        // 1. Kolom jebakan honeypot harus selalu kosong
        if (! empty($this->input('_hp_website'))) {
            throw ValidationException::withMessages([
                'email' => 'Terdeteksi aktivitas bot yang tidak wajar.',
            ]);
        }

        // 2. Proteksi kecepatan submit: manusia butuh minimal 2 detik untuk mengisi form
        $timestamp = (int) $this->input('_hp_timestamp');
        if ($timestamp > 0 && (time() - $timestamp) < 2) {
            throw ValidationException::withMessages([
                'email' => 'Formulir dikirim terlalu cepat. Mohon coba kembali.',
            ]);
        }
    }
}
