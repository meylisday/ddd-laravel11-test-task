<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repositories;

use Modules\Invoices\Domain\Models\Invoice;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function find(string $id): ?Invoice
    {
        return Invoice::with('productLines')->find($id);
    }

    public function save(Invoice $invoice): void
    {
        $invoice->save();
    }

    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }
}