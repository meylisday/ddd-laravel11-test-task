<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'product_lines' => 'nullable|array',
            'product_lines.*.name' => 'required_with:product_lines|string|max:255',
            'product_lines.*.quantity' => 'required_with:product_lines|integer|min:1',
            'product_lines.*.price' => 'required_with:product_lines|integer|min:1',
        ];
    }
}
