<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Invoices\Domain\Exceptions\DomainRuleViolationException;
use Modules\Invoices\Domain\Exceptions\NotFoundException;
use Modules\Invoices\Domain\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function view(string $id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->viewInvoice($id);
            return response()->json($invoice);
        } catch (NotFoundException $e) {
            throw new NotFoundException($e->getMessage());
        }
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'product_lines' => 'required|array|min:1',
            'product_lines.*.product_name' => 'required|string|max:255',
            'product_lines.*.quantity' => 'required|integer|min:1',
            'product_lines.*.unit_price' => 'required|integer|min:1',
        ]);

        $invoice = $this->invoiceService->createInvoice(
            $validated['customer_name'],
            $validated['customer_email'],
            $validated['product_lines'],
        );

        return response()->json($invoice, 201);
    }

    public function send(string $id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->sendInvoice($id);
            return response()->json($invoice);
        } catch (NotFoundException $e) {
            throw new NotFoundException($e->getMessage());
        } catch (DomainRuleViolationException $e) {
            throw new DomainRuleViolationException($e->getMessage());
        }
    }

}
