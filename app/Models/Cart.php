<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coupon_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function calculateTotals(): array
    {
        $this->loadMissing(['items.product', 'coupon']);

        $subtotal = 0.0;
        foreach ($this->items as $item) {
            $unitPrice = $item->product ? $item->product->sale_price : (float) $item->unit_price;
            $subtotal += $unitPrice * $item->quantity;
        }

        $discount = 0.0;
        if ($this->coupon && $this->coupon->isValid($subtotal)) {
            $discount = $this->coupon->calculateDiscount($subtotal);
        }

        $taxableAmount = max(0, $subtotal - $discount);
        $tax = round($taxableAmount * 0.10, 2); // 10% tax
        $shipping = ($subtotal > 0 && $subtotal < 50) ? 3.00 : 0.00; // Free shipping over $50
        $grandTotal = round(max(0, $taxableAmount + $tax + $shipping), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'grand_total' => round($grandTotal, 2),
            'coupon_code' => $this->coupon_code,
        ];
    }
}
