<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Invoices\Domain\Enums\StatusEnum;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'status',
        'customer_name',
        'customer_email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function productLines(): HasMany
    {
        return $this->hasMany(InvoiceProductLine::class);
    }
}