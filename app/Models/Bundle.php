<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'image', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(BundleItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // Harga normal (total semua produk tanpa diskon)
    public function getNormalPriceAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->product->price * $item->quantity);
    }

    // Hemat berapa
    public function getSavingAttribute(): float
    {
        return max(0, $this->normal_price - $this->price);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
