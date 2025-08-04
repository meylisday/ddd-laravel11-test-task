<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\Controllers\InvoiceController;
use Modules\Notifications\Presentation\Http\NotificationController;
use Ramsey\Uuid\Validator\GenericValidator;

Route::pattern('action', '^[a-zA-Z]+$');
Route::pattern('reference', (new GenericValidator)->getPattern());

Route::get('/invoices/{invoice}', [InvoiceController::class, 'view'])->name('invoices.view');
Route::post('/invoices', [InvoiceController::class, 'create'])->name('invoices.create');
Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');