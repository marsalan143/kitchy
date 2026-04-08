<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Scopes\BranchScope;

class Order extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $fillable = [
        'uuid',
        'branch_id',
        'order_number',
        'quotation_id',
        'customer_id',
        'event_date',
        'food_preparation_start_time',
        'food_delivery_time',
        'event_start_time',
        'delivery_status',
        'delivery_notes',
        'subtotal',
        'gst_enabled',
        'gst_percentage',
        'gst_amount',
        'discount',
        'grand_total',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'food_preparation_start_time' => 'datetime',
        'food_delivery_time' => 'datetime',
        'event_start_time' => 'datetime',
        'subtotal' => 'decimal:2',
        'gst_enabled' => 'boolean',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->branch_id) && auth()->check() && auth()->user()->branch_id) {
                $model->branch_id = auth()->user()->branch_id;
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OrderActivity::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
