<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Aggregates;

use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\DomainRuleViolationException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\ValueObjects\ProductLine;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;

final class InvoiceAggregate
{
    public function __construct(
        private readonly InvoiceId $id,
        private readonly StatusEnum $status,
        private readonly string $customerName,
        private readonly string $customerEmail,
        /** @var ProductLine[] */
        private readonly array $productLines = [],
    ) {}

    public function id(): InvoiceId
    {
        return $this->id;
    }

    public function isDraft(): bool
    {
        return $this->status === StatusEnum::DRAFT;
    }

    public function isSending(): bool
    {
        return $this->status === StatusEnum::SENDING;
    }

    public function isSentToClient(): bool
    {
        return $this->status === StatusEnum::SENT_TO_CLIENT;
    }

    public function getStatus(): StatusEnum
    {
        return $this->status;
    }

    public function hasValidProductLines(): bool
    {
        if (empty($this->productLines)) {
            return true;
        }

        return collect($this->productLines)->every(function (ProductLine $line) {
            return $line->quantity() > 0 && $line->price() > 0;
        });
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function customerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getProductLines(): array
    {
        return $this->productLines;
    }

    public function totalPrice(): int
    {
        return collect($this->productLines)->sum(fn (ProductLine $line) => $line->totalPrice());
    }

    public static function fromModel(Invoice $invoiceModel): self
    {
        $productLines = collect($invoiceModel->productLines)->map(function ($lineModel) {
            return new ProductLine(
                name: $lineModel->name,
                quantity: $lineModel->quantity,
                price: $lineModel->price,
            );
        })->all();


        return new self(
            id: new InvoiceId($invoiceModel->id),
            status: StatusEnum::from($invoiceModel->status),
            customerName: $invoiceModel->customer_name,
            customerEmail: $invoiceModel->customer_email,
            productLines: $productLines,
        );
    }

    public function toModel(?Invoice $model = null): Invoice
    {
        $invoice = $model ?? new Invoice();
        $invoice->id = $this->id->value();
        $invoice->customer_name = $this->customerName;
        $invoice->customer_email = $this->customerEmail;
        $invoice->status = $this->status->value;

        return $invoice;
    }

    public function markAsSending(): self
    {
        if (!$this->isDraft()) {
            throw new DomainRuleViolationException('Invoice must be in draft status to be sent.');
        }

        if (!empty($this->productLines) && !$this->hasValidProductLines()) {
            throw new DomainRuleViolationException(
                'Invoice must have valid product lines (positive quantity and unit price).'
            );
        }

        return new self(
            id: $this->id,
            status: StatusEnum::SENDING,
            customerName: $this->customerName,
            customerEmail: $this->customerEmail,
            productLines: $this->productLines,
        );
    }

    public function markAsSentToClient(): self
    {
        if (!$this->isSending()) {
            throw new DomainRuleViolationException('Invoice must be in sending status to be marked as sent-to-client.');
        }

        return new self(
            id: $this->id,
            status: StatusEnum::SENT_TO_CLIENT,
            customerName: $this->customerName,
            customerEmail: $this->customerEmail,
            productLines: $this->productLines,
        );
    }

    public static function createDraft(
        InvoiceId $id,
        string $customerName,
        string $customerEmail,
        array $productLines = []
    ): self {
        return new self(
            id: $id,
            status: StatusEnum::DRAFT,
            customerName: $customerName,
            customerEmail: $customerEmail,
            productLines: $productLines,
        );
    }

    public function withProductLines(array $productLines): self
    {
        return new self(
            id: $this->id,
            status: $this->status,
            customerName: $this->customerName,
            customerEmail: $this->customerEmail,
            productLines: $productLines,
        );
    }
}