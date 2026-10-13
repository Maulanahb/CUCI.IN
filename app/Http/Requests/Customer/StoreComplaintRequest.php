<?php

namespace App\Http\Requests\Customer;

use App\Enums\TransactionStatus;
use App\Models\Complaint;
use App\Models\Transaction;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->role === 'customer' && $user->customer !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->user()?->customer?->id;

        return [
            'transaction_id' => [
                'required',
                'integer',
                Rule::exists('transactions', 'id')->where(function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                }),
                function (string $attribute, mixed $value, Closure $fail): void {
                    /** @var Transaction|null $transaction */
                    $transaction = Transaction::find($value);
                    if (! $transaction) {
                        return;
                    }

                    // 1. Status transaksi tidak boleh Dibatalkan
                    $status = $transaction->status instanceof TransactionStatus
                        ? $transaction->status->value
                        : (string) $transaction->status;

                    if ($status === 'Dibatalkan') {
                        $fail('Pengaduan tidak dapat diajukan untuk transaksi yang telah dibatalkan.');

                        return;
                    }

                    // 2. Anti-spam: tidak boleh memiliki komplain aktif yang belum selesai
                    $hasActiveComplaint = Complaint::where('transaction_id', $transaction->id)
                        ->whereIn('status', ['Menunggu', 'Diproses'])
                        ->exists();

                    if ($hasActiveComplaint) {
                        $fail('Nota transaksi ini masih memiliki pengaduan aktif yang sedang ditangani.');
                    }
                },
            ],
            'subject' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
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
            'transaction_id.required' => 'Pilih nota transaksi yang ingin Anda laporkan.',
            'transaction_id.exists' => 'Nota transaksi tidak valid atau tidak terdaftar pada akun Anda.',
            'subject.required' => 'Subjek pengaduan wajib diisi.',
            'subject.min' => 'Subjek pengaduan minimal 5 karakter.',
            'subject.max' => 'Subjek pengaduan maksimal 150 karakter.',
            'description.required' => 'Rincian kendala pengaduan wajib diisi.',
            'description.min' => 'Rincian kendala minimal 10 karakter.',
            'description.max' => 'Rincian kendala maksimal 2.000 karakter.',
        ];
    }
}
