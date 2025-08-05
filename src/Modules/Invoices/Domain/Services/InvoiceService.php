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
        $invoiceId = new InvoiceId((string) Str::uuid());

        $productLines = array_map(fn($line) => new ProductLine(
            productName: $line['product_name'],
            quantity: $line['quantity'],
            unitPrice: $line['unit_price'],
        ), $dto->productLines);

        $aggregate = InvoiceAggregate::createDraft(
            id: $invoiceId,
            customerName: $dto->customerName,
            customerEmail: $dto->customerEmail,
            productLines: $dto->productLines,
        );

        $invoiceModel = $aggregate->toModel();
        $this->repository->save($invoiceModel);

        foreach ($productLines as $line) {
            $invoiceModel->productLines()->create([
                'product_name' => $line->productName(),
                'quantity' => $line->quantity(),
                'unit_price' => $line->unitPrice(),
            ]);
        }

        $invoiceModel->refresh();

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

        $updatedModel = $aggregate->toModel($invoice);
        $this->repository->save($updatedModel);

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
                'product_name' => $line->productName(),
                'quantity' => $line->quantity(),
                'unit_price' => $line->unitPrice(),
                'total_unit_price' => $line->totalPrice(),
            ])->toArray(),
            'total_price' => $aggregate->totalPrice(),
        ];
    }
}