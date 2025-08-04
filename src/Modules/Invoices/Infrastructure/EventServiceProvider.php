<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Invoices\Application\Listeners\ResourceDeliveredListener;

final class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        Event::listen(
            ResourceDeliveredListener::class,
            ResourceDeliveredListener::class,
        );
    }
}
