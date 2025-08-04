<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceProductLine extends Model
{
    protected $table = 'invoice_product_lines';

    protected $fillable = [
        'invoice_id',
        'product_name',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTotalUnitPriceAttribute(): int
    {
        return $this->quantity * $this->unit_price;
    }
}