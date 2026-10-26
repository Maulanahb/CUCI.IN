<?php

namespace App\Http\Requests\Customer;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentProofRequest extends FormRequest
{
    /**
     * Hanya pemilik transaksi yang boleh mengunggah bukti pembayaran.
     */
    public function authorize(): bool
    {
        $customerId = $this->user()?->customer?->id;
        $transaction = $this->route('transaction');

        return $customerId !== null
            && $transaction instanceof Transaction
            && $transaction->customer_id === $customerId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'method' => ['required', 'string', Rule::in(Payment::PROOF_METHODS)],
            'amount' => ['required', 'numeric', 'min:100'],
            'proof_file' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:min_width=200,min_height=200',
            ],
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
            'method.required' => 'Pilih metode pembayaran yang kamu gunakan.',
            'method.in' => 'Bukti pembayaran hanya untuk metode Transfer atau QRIS.',
            'amount.required' => 'Nominal yang kamu bayarkan wajib diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka.',
            'amount.min' => 'Nominal pembayaran minimal Rp 100.',
            'proof_file.required' => 'Unggah foto bukti transfer atau struk QRIS dulu, ya.',
            'proof_file.file' => 'Bukti pembayaran harus berupa berkas.',
            'proof_file.image' => 'Bukti pembayaran harus berupa gambar.',
            'proof_file.mimes' => 'Format bukti pembayaran hanya JPG, JPEG, PNG, atau WEBP.',
            'proof_file.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
            'proof_file.dimensions' => 'Gambar bukti terlalu kecil. Gunakan gambar minimal 200×200 piksel agar terbaca jelas.',
        ];
    }
}
