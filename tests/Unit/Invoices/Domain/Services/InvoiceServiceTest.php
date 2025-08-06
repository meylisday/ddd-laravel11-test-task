<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Services;

use Illuminate\Support\Str;
use Mockery;
use Modules\Invoices\Application\DTO\CreateInvoiceDTO;
use Modules\Invoices\Domain\Aggregates\InvoiceAggregate;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepository;
use Modules\Invoices\Domain\Services\InvoiceService;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Tests\TestCase;
use Illuminate\Support\Collection;

final class InvoiceServiceTest extends TestCase
{
    public function test_it_creates_invoice_successfully()
    {
        $repository = Mockery::mock(InvoiceRepository::class);
        $repository->expects('saveAggregate')
            ->andReturnUsing(function(InvoiceAggregate $aggregate) {
                $model = $aggregate->toModel();
                $model->id = (string) Str::uuid();
                $model->customer_name = $aggregate->customerName();
                $model->customer_email = $aggregate->customerEmail();

                $line = new InvoiceProductLine();
                $line->name = 'TestProduct';
                $line->quantity = 1;
                $line->price = 100;

                $model->setRelation('productLines', new Collection([$line]));

                return $model;
            });

        $notificationFacade = Mockery::mock(NotificationFacadeInterface::class);

        $service = new InvoiceService($repository, $notificationFacade);

        $dto = new CreateInvoiceDTO(
            customerName: 'TestName',
            customerEmail: 'testname@example.com',
            productLines: [
                ['name' => 'TestProduct', 'quantity' => 1, 'price' => 100],
            ],
        );

        $result = $service->createInvoice($dto);

        $this->assertSame('TestName', $result['customer_name']);
        $this->assertCount(1, $result['product_lines']);
        $this->assertSame('TestProduct', $result['product_lines'][0]['name']);
    }
}