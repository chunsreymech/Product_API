<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Vendor;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_id', 'category_id', 'name', 'slug', 'sku', 'description', 'price', 'discount_price', 'stock', 'image', 'status', 'featured'];
    protected $casts = ['price' => 'decimal:2', 'discount_price' => 'decimal:2', 'featured' => 'boolean'];
    public function vendor() { return $this->belongsTo(Vendor::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function getSalePriceAttribute() { return $this->discount_price ?? $this->price; }
}
