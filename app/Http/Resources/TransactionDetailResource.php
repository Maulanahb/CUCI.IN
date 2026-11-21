<?php

namespace App\Http\Resources;

use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransactionDetail
 */
class TransactionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_name' => $this->whenLoaded('service', fn () => $this->service?->name),
            'service_type' => $this->whenLoaded('service', fn () => $this->service?->service_type),
            'service_category' => $this->whenLoaded('service', fn () => $this->service?->service_type),
            'is_express' => $this->whenLoaded('service', fn () => (bool) $this->service?->is_express),
            'weight' => (float) $this->weight,
            'unit' => $this->whenLoaded('service', fn () => $this->service?->unit ?? 'kg', 'kg'),
            'unit_price' => (int) round((float) $this->unit_price),
            'subtotal' => (int) round((float) $this->subtotal),
        ];
    }
}
