<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Listeners;

use Modules\Invoices\Domain\Aggregates\InvoiceAggregate;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;

class ResourceDeliveredListener
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
    ) {}

    public function __invoke(ResourceDeliveredEvent $event): void
    {
        $invoice = $this->repository->find($event->resourceId->toString());

        if (!$invoice) {
            return;
        }

        $aggregate = InvoiceAggregate::fromModel($invoice);

        if ($aggregate->getStatus() !== StatusEnum::SENDING) {
            return;
        }

        $aggregate = $aggregate->markAsSentToClient();

        $this->repository->saveAggregate($aggregate);
    }
}
