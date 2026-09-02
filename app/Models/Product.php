<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'discount_price',
        'stock',
        'image',
        'status',
        'featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'stock' => 'integer',
        'featured' => 'boolean',
    ];

    protected $appends = [
        'sale_price',
        'average_rating',
        'reviews_count',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getSalePriceAttribute(): float
    {
        return (float) ($this->discount_price !== null && $this->discount_price > 0
            ? $this->discount_price
            : $this->price);
    }

    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            $approved = $this->reviews->where('status', 'approved');
            return $approved->count() > 0 ? round((float) $approved->avg('rating'), 1) : 0.0;
        }

        $avg = $this->reviews()->where('status', 'approved')->avg('rating');
        return $avg ? round((float) $avg, 1) : 0.0;
    }

    public function getReviewsCountAttribute(): int
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->where('status', 'approved')->count();
        }

        return $this->reviews()->where('status', 'approved')->count();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }
}
