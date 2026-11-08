<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'transaction_code' => $this->transaction_code,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'name' => $this->customer?->name,
                    'phone' => $this->customer?->phone,
                ];
            }),
            'total_amount' => (int) round((float) $this->total_amount),
            'total_paid' => (int) round((float) $this->total_paid),
            'remaining_amount' => (int) round((float) $this->remaining_amount),
            'is_paid' => $this->remaining_amount <= 0,
            'estimated_completed_at' => $this->estimated_completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'last_updated_at' => $this->updated_at?->toIso8601String(),
            'items' => TransactionDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
