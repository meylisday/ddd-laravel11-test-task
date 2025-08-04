<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

final class InvoiceId
{
    private string $id;

    public function __construct(string $id)
    {
        if (!preg_match('/^[0-9a-fA-F\-]{36}$/', $id)) {
            throw new \InvalidArgumentException('Invalid Invoice ID format');
        }

        $this->id = $id;
    }

    public function value(): string
    {
        return $this->id;
    }
}