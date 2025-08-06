<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Services;

use Illuminate\Support\Str;
use Modules\Invoices\Application\DTO\CreateInvoiceDTO;
use Modules\Invoices\Domain\Aggregates\InvoiceAggregate;
use Modules\Invoices\Domain\Exceptions\NotFoundException;
use Modules\Invoices\Domain\Repositories\InvoiceRepository;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Modules\Invoices\Domain\ValueObjects\ProductLine;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepository  $repository,
        private readonly NotificationFacadeInterface $notificationFacade
    ) {}

    public function createInvoice(CreateInvoiceDTO $dto): array
    {
        $aggregate = InvoiceAggregate::createDraft(
            id: new InvoiceId((string) Str::uuid()),
            customerName: $dto->customerName,
            customerEmail: $dto->customerEmail,
            productLines: array_map(fn($line) => new ProductLine(
                name: $line['name'],
                quantity: $line['quantity'],
                price: $line['price'],
            ), $dto->productLines),
        );

        $invoiceModel = $this->repository->saveAggregate($aggregate);

        $aggregate = InvoiceAggregate::fromModel($invoiceModel);

        return $this->toArray($aggregate);
    }

    public function sendInvoice(string $invoiceId): array
    {
        $invoice = $this->repository->find($invoiceId);

        if (!$invoice) {
            throw new NotFoundException('Invoice not found');
        }

        $this->notificationFacade->notify(new NotifyData(
            resourceId: $invoice->id,
            toEmail: $invoice->customer_email,
            subject: 'Invoice is being sent',
            message: 'Thank you! Your invoice is now being processed.',
        ));

        $aggregate = InvoiceAggregate::fromModel($invoice)->markAsSending();

        $updatedModel = $this->repository->saveAggregate($aggregate);

        $aggregate = InvoiceAggregate::fromModel($updatedModel);

        return $this->toArray($aggregate);
    }

    public function viewInvoice(string $invoiceId): array
    {
        $invoice = $this->repository->find($invoiceId);
        if (!$invoice) {
            throw new NotFoundException('Invoice not found');
        }

        $aggregate = InvoiceAggregate::fromModel($invoice);

        return $this->toArray($aggregate);
    }

    private function toArray(InvoiceAggregate $aggregate): array
    {
        return [
            'id' => $aggregate->id()->value(),
            'status' => $aggregate->getStatus()->value,
            'customer_name' => $aggregate->customerName(),
            'customer_email' => $aggregate->customerEmail(),
            'product_lines' => collect($aggregate->getProductLines())->map(fn($line) => [
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'price' => $line->price(),
                'total_unit_price' => $line->totalPrice(),
            ])->toArray(),
            'total_price' => $aggregate->totalPrice(),
        ];
    }
}