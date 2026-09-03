<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCartOnLogin
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * Handle the login event by folding any guest session cart into
     * the now-authenticated customer's database cart.
     */
    public function handle(Login $event): void
    {
        $this->cartService->mergeGuestCartIntoUser($event->user);
    }
}
