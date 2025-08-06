<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Presentation\Http\Controllers;

use Modules\Invoices\Domain\Exceptions\DomainRuleViolationException;
use Modules\Invoices\Domain\Exceptions\NotFoundException;
use Modules\Invoices\Domain\Services\InvoiceService;
use Modules\Invoices\Presentation\Http\Controllers\InvoiceController;
use Tests\TestCase;
use Illuminate\Http\JsonResponse;

final class InvoiceControllerTest extends TestCase
{
    private InvoiceService $service;
    private InvoiceController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->createMock(InvoiceService::class);
        $this->controller = new InvoiceController($this->service);
    }

    public function testViewReturnsInvoice(): void
    {
        $invoiceData = ['id' => '1', 'customer_name' => 'TestName'];
        $this->service
            ->expects($this->once())
            ->method('viewInvoice')
            ->with('1')
            ->willReturn($invoiceData);

        $response = $this->controller->view('1');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($invoiceData, $response->getData(true));
    }

    public function testViewThrowsNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->service
            ->method('viewInvoice')
            ->willThrowException(new NotFoundException('Invoice not found'));

        $this->controller->view('non-existent-id');
    }

    public function testSendReturnsInvoice(): void
    {
        $invoice = ['id' => '1', 'status' => 'sent'];

        $this->service
            ->expects($this->once())
            ->method('sendInvoice')
            ->with('1')
            ->willReturn($invoice);

        $response = $this->controller->send('1');

        $this->assertEquals($invoice, $response->getData(true));
    }

    public function testSendThrowsNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->service
            ->method('sendInvoice')
            ->willThrowException(new NotFoundException('Not found'));

        $this->controller->send('404');
    }

    public function testSendThrowsDomainException(): void
    {
        $this->expectException(DomainRuleViolationException::class);

        $this->service
            ->method('sendInvoice')
            ->willThrowException(new DomainRuleViolationException('Invoice already sent'));

        $this->controller->send('1');
    }
}