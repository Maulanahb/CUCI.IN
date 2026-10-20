<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'amount' => ['required', 'numeric', 'min:1'],
            'cash_received' => ['nullable', 'numeric', 'min:0', 'gte:amount', 'exclude_unless:method,Tunai'],
            'method' => ['required', 'string', 'in:Tunai,Transfer,QRIS'],
            'payment_type' => ['nullable', 'string', 'in:Pembayaran Penuh,DP,Pelunasan'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka.',
            'amount.min' => 'Nominal pembayaran minimal Rp 1.',
            'cash_received.gte' => 'Uang diterima tidak boleh kurang dari nominal pembayaran.',
            'method.required' => 'Metode pembayaran wajib dipilih.',
            'method.in' => 'Metode pembayaran harus berupa Tunai, Transfer, atau QRIS.',
        ];
    }
}
