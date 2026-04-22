<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'user_id', 'customer_id',
        'subtotal', 'discount', 'tax', 'total',
        'paid_amount', 'change_amount', 'payment_method', 'status', 'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public static function generateInvoice(): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $last = static::where('invoice_number', 'like', $prefix . '%')->latest()->first();
        $number = $last ? (int) substr($last->invoice_number, -4) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
