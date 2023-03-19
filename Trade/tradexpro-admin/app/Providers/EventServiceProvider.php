<?php

namespace App\Providers;

use App\Model\WithdrawHistory;
use App\Model\DepositeTransaction;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Observers\WithdrawHistoryObserver;
use App\Observers\DepositeTransactionObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\OrderHasPlaced::class => [
            \App\Listeners\StartProcessingOrder::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        DepositeTransaction::observe(DepositeTransactionObserver::class);
        WithdrawHistory::observe(WithdrawHistoryObserver::class);
    }
}
