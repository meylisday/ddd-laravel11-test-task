<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repositories;

use Modules\Invoices\Domain\Aggregates\InvoiceAggregate;
use Modules\Invoices\Domain\Models\Invoice;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function find(string $id): ?Invoice
    {
        return Invoice::with('productLines')->find($id);
    }

    public function saveAggregate(InvoiceAggregate $aggregate): Invoice
    {
        $invoiceModel = $aggregate->toModel();

        $invoiceModel->save();

        $invoiceModel->productLines()->delete();

        foreach ($aggregate->getProductLines() as $line) {
            $invoiceModel->productLines()->create([
                'name' => $line->productName(),
                'quantity' => $line->quantity(),
                'price' => $line->unitPrice(),
            ]);
        }

        $invoiceModel->refresh();

        return $invoiceModel;
    }
}