<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentUnitPrice = $this->product ? (float) $this->product->sale_price : (float) $this->unit_price;
        $subtotal = round($currentUnitPrice * (int) $this->quantity, 2);

        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'unit_price' => $currentUnitPrice,
            'subtotal' => $subtotal,
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
