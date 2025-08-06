<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

final class ProductLine
{
    private string $name;
    private int $quantity;
    private int $price;

    public function __construct(string $name, int $quantity, int $price)
    {
        if ($quantity <= 0 || $price <= 0) {
            throw new \InvalidArgumentException('Quantity and unit price must be positive integers.');
        }

        $this->name = $name;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function totalPrice(): int
    {
        return $this->quantity * $this->price;
    }
}