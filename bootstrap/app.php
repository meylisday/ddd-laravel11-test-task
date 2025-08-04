<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\Invoices\Domain\Exceptions\DomainRuleViolationException;
use Modules\Invoices\Domain\Exceptions\NotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundException $e) {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        });

        $exceptions->renderable(function (DomainRuleViolationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        });
    })->create();
