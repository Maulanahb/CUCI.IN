<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    /**
     * Tentukan apakah user berwenang melakukan aksi ini (Hanya Admin).
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Persiapkan dan normalisasi input sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $rawPrice = (string) $this->input('price');
            // Bersihkan format mata uang seperti "Rp", titik ribuan, atau spasi
            $cleanedPrice = str_replace(['Rp', 'rp', ' ', '.'], '', $rawPrice);
            $cleanedPrice = str_replace(',', '.', $cleanedPrice);
            $this->merge(['price' => $cleanedPrice]);
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
            ]);
        }
    }

    /**
     * Aturan validasi data layanan laundry.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $serviceId = $this->route('service')?->id ?? $this->route('service');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('services', 'name')->ignore($serviceId),
            ],
            'price' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'estimated_duration' => [
                'required',
                'integer',
                'min:1',
                'max:720',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Pesan kesalahan khusus validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama layanan laundry wajib diisi.',
            'name.string' => 'Nama layanan laundry harus berupa teks.',
            'name.max' => 'Nama layanan laundry maksimal 100 karakter.',
            'name.unique' => 'Nama layanan laundry sudah digunakan. Gunakan nama lain.',
            'price.required' => 'Harga layanan wajib diisi.',
            'price.numeric' => 'Harga layanan harus berupa angka valid.',
            'price.gt' => 'Harga layanan harus lebih besar dari 0.',
            'estimated_duration.required' => 'Estimasi durasi pengerjaan wajib diisi.',
            'estimated_duration.integer' => 'Estimasi durasi pengerjaan harus berupa angka bulat dalam jam.',
            'estimated_duration.min' => 'Estimasi durasi pengerjaan minimal 1 jam.',
            'estimated_duration.max' => 'Estimasi durasi pengerjaan maksimal 720 jam (30 hari).',
            'description.max' => 'Deskripsi layanan maksimal 1000 karakter.',
        ];
    }

    /**
     * Nama atribut yang mudah dibaca pengguna.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama layanan',
            'price' => 'harga layanan',
            'estimated_duration' => 'estimasi durasi',
            'description' => 'deskripsi layanan',
            'is_active' => 'status aktif',
        ];
    }
}
