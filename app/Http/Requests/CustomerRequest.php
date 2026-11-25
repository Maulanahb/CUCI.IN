<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            && in_array($this->user()->role, ['admin', 'staff'], true);
    }

    /**
     * Normalisasi nomor telepon sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => static::normalizePhoneNumber(
                    $this->input('phone')
                ),
            ]);
        }
    }

    /**
     * Mengubah nomor telepon ke format lokal 08...
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (! $phone) {
            return $phone;
        }

        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

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

    public function rules(): array
    {
        // Mendapatkan pelanggan saat proses update.
        $customer = $this->route('customer');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/',

                // Nomor telepon tidak boleh digunakan pelanggan lain.
                Rule::unique('customers', 'phone')
                    ->ignore($customer?->id),
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'name.max' => 'Nama pelanggan maksimal 100 karakter.',

            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon harus berupa nomor seluler Indonesia yang valid (contoh: 081234567890).',
            'phone.unique' => 'Nomor telepon sudah terdaftar. Gunakan nomor telepon lain.',

            'address.max' => 'Alamat maksimal 500 karakter.',
        ];
    }
}
