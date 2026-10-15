<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'staff'], true);
    }

    /**
     * Persiapkan dan normalisasi input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => static::normalizePhoneNumber($this->input('phone')),
            ]);
        }
    }

    /**
     * Normalisasi nomor telepon ke format lokal standar "08...".
     *
     * Contoh:
     * - "+6281234567890"    -> "081234567890"
     * - "+62 812-3456-7890" -> "081234567890"
     * - "6281234567890"     -> "081234567890"
     * - "81234567890"       -> "081234567890"
     * - "081234567890"      -> "081234567890"
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (! $phone) {
            return $phone;
        }

        $phone = trim($phone);

        // Bersihkan spasi, titik, strip, atau tanda kurung
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($cleaned, '+62')) {
            $cleaned = substr($cleaned, 3);
            if (! str_starts_with($cleaned, '0')) {
                $cleaned = '0'.$cleaned;
            }
        } elseif (str_starts_with($cleaned, '62')) {
            $cleaned = substr($cleaned, 2);
            if (! str_starts_with($cleaned, '0')) {
                $cleaned = '0'.$cleaned;
            }
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '0'.$cleaned;
        }

        return ltrim($cleaned, '+');
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
            'address' => ['nullable', 'string', 'max:500'],
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
            'name.required' => 'Nama pelanggan wajib diisi.',
            'name.max' => 'Nama pelanggan maksimal 100 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
        ];
    }
}
