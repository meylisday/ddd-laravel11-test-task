<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Invoices\Application\DTO\CreateInvoiceDTO;
use Modules\Invoices\Domain\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Modules\Invoices\Presentation\Http\Requests\CreateInvoiceRequest;
use Symfony\Component\HttpFoundation\Response;

final class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function view(string $id): JsonResponse
    {
        $invoice = $this->invoiceService->viewInvoice($id);

        return response()->json($invoice);
    }

    public function create(CreateInvoiceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new CreateInvoiceDTO(
            customerName: $validated['customer_name'],
            customerEmail: $validated['customer_email'],
            productLines: $validated['product_lines'],
        );

        $invoice = $this->invoiceService->createInvoice($dto);

        return response()->json($invoice, Response::HTTP_CREATED);
    }

    public function send(string $id): JsonResponse
    {
        $invoice = $this->invoiceService->sendInvoice($id);

        return response()->json($invoice);
    }

}
