<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:100',
            ],

            'method' => [
                'required',
                'string',
                Rule::in(['Tunai', 'Transfer', 'QRIS']),
            ],

            'payment_type' => [
                'nullable',
                'string',
                Rule::in([
                    'Pembayaran Penuh',
                    'DP',
                    'Pelunasan',
                ]),
            ],

            'cash_received' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:amount',
                'exclude_unless:method,Tunai',
            ],

            'proof_file' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka.',
            'amount.min' => 'Nominal pembayaran minimal Rp 100.',

            'method.required' => 'Metode pembayaran wajib dipilih.',
            'method.in' => 'Metode pembayaran harus berupa Tunai, Transfer, atau QRIS.',

            'payment_type.in' => 'Jenis pembayaran tidak valid.',

            'cash_received.numeric' => 'Uang diterima harus berupa angka.',
            'cash_received.min' => 'Uang diterima tidak boleh negatif.',
            'cash_received.gte' => 'Uang diterima tidak boleh kurang dari nominal pembayaran.',

            'proof_file.file' => 'Bukti pembayaran harus berupa berkas.',
            'proof_file.image' => 'Bukti pembayaran harus berupa gambar.',
            'proof_file.mimes' => 'Bukti pembayaran harus berformat JPG, JPEG, PNG, atau WEBP.',
            'proof_file.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
        ];
    }
}
