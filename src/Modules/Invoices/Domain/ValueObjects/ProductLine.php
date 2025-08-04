<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

final class ProductLine
{
    private string $productName;
    private int $quantity;
    private int $unitPrice;

    public function __construct(string $productName, int $quantity, int $unitPrice)
    {
        if ($quantity <= 0 || $unitPrice <= 0) {
            throw new \InvalidArgumentException('Quantity and unit price must be positive integers.');
        }

        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): int
    {
        return $this->unitPrice;
    }

    public function totalPrice(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}