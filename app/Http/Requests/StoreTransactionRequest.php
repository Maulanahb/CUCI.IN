<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Otorisasi role ditangani oleh middleware pada rute.
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
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id'),
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'items.*.weight' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'pay_now' => [
                'nullable',
                'boolean',
            ],
            'payment_method' => [
                'nullable',
                'required_if:pay_now,true',
                'string',
                'in:Tunai,Transfer,QRIS',
            ],
            'payment_amount' => [
                'nullable',
                'numeric',
                'min:0',
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
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'customer_id.integer' => 'ID pelanggan tidak valid.',
            'customer_id.exists' => 'Pelanggan yang dipilih tidak ditemukan.',
            'items.required' => 'Daftar item cucian wajib diisi.',
            'items.array' => 'Daftar item cucian harus berupa array.',
            'items.min' => 'Daftar item cucian minimal berisi 1 item.',
            'items.*.service_id.required' => 'Layanan cucian wajib dipilih.',
            'items.*.service_id.integer' => 'ID layanan tidak valid.',
            'items.*.service_id.exists' => 'Layanan tidak valid atau sedang tidak aktif.',
            'items.*.weight.required' => 'Berat cucian wajib diisi.',
            'items.*.weight.numeric' => 'Berat cucian harus berupa angka.',
            'items.*.weight.gt' => 'Berat cucian harus lebih besar dari 0.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'pelanggan',
            'items' => 'item transaksi',
            'items.*.service_id' => 'layanan',
            'items.*.weight' => 'berat cucian',
        ];
    }
}
