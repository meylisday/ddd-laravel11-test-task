<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\DTO;

final readonly class CreateInvoiceDTO
{
    /**
     * @param array<int, array{name: string, quantity: int, price: int}> $productLines
     */
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        public array $productLines,
    ) {}
}