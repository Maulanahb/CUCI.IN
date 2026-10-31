<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuestPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_code' => [
                'required',
                'string',
                'exists:transactions,transaction_code',
            ],
            'phone_last_4' => [
                'required',
                'string',
                'digits:4',
            ],
            'method' => [
                'required',
                'string',
                Rule::in(['Transfer', 'QRIS']),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:100',
            ],
            'proof_file' => [
                'required',
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
            'transaction_code.required' => 'Nomor nota harus diisi.',
            'transaction_code.exists' => 'Nomor nota tidak ditemukan di sistem kami.',
            'phone_last_4.required' => '4 digit terakhir nomor telepon harus diisi sebagai verifikasi keamanan.',
            'phone_last_4.digits' => 'Masukkan tepat 4 digit terakhir nomor telepon yang terdaftar pada nota.',
            'method.required' => 'Silakan pilih metode pembayaran (Transfer Bank atau QRIS).',
            'method.in' => 'Metode pembayaran harus berupa Transfer Bank atau QRIS.',
            'amount.required' => 'Nominal pembayaran harus diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka valid.',
            'amount.min' => 'Nominal pembayaran minimal Rp 100.',
            'proof_file.required' => 'Foto bukti transfer atau struk QRIS wajib dilampirkan.',
            'proof_file.image' => 'Berkas bukti pembayaran harus berupa gambar.',
            'proof_file.mimes' => 'Format gambar yang didukung adalah JPG, JPEG, PNG, atau WEBP.',
            'proof_file.max' => 'Ukuran berkas bukti pembayaran maksimal 5 MB.',
        ];
    }
}
