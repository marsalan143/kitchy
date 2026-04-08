<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Scopes\BranchScope;

class Quotation extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $fillable = [
        'uuid',
        'branch_id',
        'quotation_number',
        'revision_number',
        'customer_id',
        'event_date',
        'subtotal',
        'gst_enabled',
        'gst_percentage',
        'gst_amount',
        'discount',
        'grand_total',
        'status',
        'expiry_date',
        'parent_quotation_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'expiry_date' => 'date',
        'subtotal' => 'decimal:2',
        'gst_enabled' => 'boolean',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'revision_number' => 'integer',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
