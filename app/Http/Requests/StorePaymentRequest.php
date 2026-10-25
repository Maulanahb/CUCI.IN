<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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
                Rule::in([
                    'Tunai',
                    'Transfer',
                    'QRIS',
                ]),
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

            /*
             * Bukti pembayaran:
             * - Tunai      -> tidak wajib
             * - Transfer   -> wajib
             * - QRIS       -> wajib
             */
            'proof_file' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'required_if:method,Transfer,QRIS',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'amount.required' =>
                'Nominal pembayaran wajib diisi.',

            'amount.numeric' =>
                'Nominal pembayaran harus berupa angka.',

            'amount.min' =>
                'Nominal pembayaran minimal Rp 100.',

            'method.required' =>
                'Metode pembayaran wajib dipilih.',

            'method.in' =>
                'Metode pembayaran harus berupa Tunai, Transfer, atau QRIS.',

            'proof_file.required_if' =>
                'Bukti pembayaran wajib diunggah untuk pembayaran Transfer atau QRIS.',

            'proof_file.file' =>
                'Bukti pembayaran harus berupa berkas.',

            'proof_file.image' =>
                'Bukti pembayaran harus berupa gambar.',

            'proof_file.mimes' =>
                'Bukti pembayaran harus berformat JPG, JPEG, PNG, atau WEBP.',

            'proof_file.max' =>
                'Ukuran bukti pembayaran maksimal 5 MB.',
        ];
    }
}