<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repositories;

use Modules\Invoices\Domain\Aggregates\InvoiceAggregate;
use Modules\Invoices\Domain\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function find(string $id): ?Invoice;

    public function saveAggregate(InvoiceAggregate $aggregate): Invoice;
}