<?php

namespace App\Http\Requests;

use App\Services\VerifyPayment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyPaymentRequest extends FormRequest
{
    /**
     * Akses role sudah dibatasi middleware `role:admin,staff`.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'verification_status' => ['required', 'string', Rule::in([VerifyPayment::APPROVE, VerifyPayment::REJECT])],
            'verification_notes' => ['nullable', 'string', 'max:500', 'required_if:verification_status,'.VerifyPayment::REJECT],
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
            'verification_status.required' => 'Keputusan verifikasi wajib dipilih.',
            'verification_status.in' => 'Keputusan verifikasi harus Terverifikasi atau Ditolak.',
            'verification_notes.required_if' => 'Alasan penolakan wajib diisi.',
            'verification_notes.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
