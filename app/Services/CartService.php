<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CartService
{
    /**
     * The session key used to remember a guest cart's identifier.
     */
    private const SESSION_KEY = 'cart_session_id';

    /**
     * Maximum quantity allowed per cart line item.
     */
    private const MAX_QUANTITY = 100;

    /**
     * Get the total item quantity in the current customer's cart
     * without creating a new (empty) cart row if one doesn't exist.
     */
    public function currentItemCount(): int
    {
        $cart = Auth::check()
            ? Cart::query()->where('user_id', Auth::id())->first()
            : Cart::query()->where('session_id', Session::get(self::SESSION_KEY))->first();

        return $cart?->items()->sum('quantity') ?? 0;
    }

    /**
     * Get the current customer's cart, creating one if it doesn't exist yet.
     *
     * - Logged-in customer: a single cart row tied to their user_id.
     * - Guest customer: a cart row tied to a session identifier.
     */
    public function currentCart(): Cart
    {
        if (Auth::check()) {
            return $this->cartForUser(Auth::user());
        }

        return $this->cartForGuestSession();
    }

    /**
     * Get (or create) the cart belonging to a specific authenticated user.
     */
    public function cartForUser(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Get (or create) the cart belonging to the current guest session.
     */
    public function cartForGuestSession(): Cart
    {
        $sessionId = Session::get(self::SESSION_KEY);

        if (! $sessionId) {
            $sessionId = (string) Str::uuid();
            Session::put(self::SESSION_KEY, $sessionId);
        }

        return Cart::query()->firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Add a product (optionally with a specific size/color variant)
     * to the cart, or increase its quantity if it is already in the
     * cart with the same variant.
     */
    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null): CartItem
    {
        $quantity = $this->normalizeQuantity($quantity);

        $cart = $this->currentCart();

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        if ($item) {
            return $this->setQuantity($item, $item->quantity + $quantity);
        }

        $price = (float) $product->effective_price + (float) ($variant?->extra_price ?? 0);

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => min($quantity, self::MAX_QUANTITY),
            'price' => $price,
        ]);
    }

    /**
     * Increase a cart item's quantity by one (capped at the stock and
     * the max quantity per line).
     */
    public function increment(CartItem $item): CartItem
    {
        return $this->setQuantity($item, $item->quantity + 1);
    }

    /**
     * Decrease a cart item's quantity by one. Removes the item once
     * the quantity would drop to zero.
     */
    public function decrement(CartItem $item): ?CartItem
    {
        if ($item->quantity <= 1) {
            $this->removeItem($item);

            return null;
        }

        return $this->setQuantity($item, $item->quantity - 1);
    }

    /**
     * Set a cart item's quantity to an explicit value.
     */
    public function setQuantity(CartItem $item, int $quantity): CartItem
    {
        $quantity = $this->normalizeQuantity($quantity);
        $quantity = min($quantity, self::MAX_QUANTITY);

        $stockLimit = $item->variant_id
            ? $item->variant?->stock_quantity
            : $item->product->stock_quantity;

        if ($stockLimit > 0) {
            $quantity = min($quantity, $stockLimit);
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    /**
     * Remove an item from the cart entirely.
     */
    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Remove every item from the given cart.
     */
    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Merge a guest session cart into a user's cart, typically called
     * right after login/registration so items added before signing
     * in are not lost.
     */
    public function mergeGuestCartIntoUser(User $user): void
    {
        $sessionId = Session::get(self::SESSION_KEY);

        if (! $sessionId) {
            return;
        }

        $guestCart = Cart::query()->where('session_id', $sessionId)->first();

        if (! $guestCart) {
            return;
        }

        $userCart = $this->cartForUser($user);

        foreach ($guestCart->items as $guestItem) {
            $existing = CartItem::query()
                ->where('cart_id', $userCart->id)
                ->where('product_id', $guestItem->product_id)
                ->where('variant_id', $guestItem->variant_id)
                ->first();

            if ($existing) {
                $this->setQuantity($existing, $existing->quantity + $guestItem->quantity);
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Keep quantities sane: always a positive integer.
     */
    private function normalizeQuantity(int $quantity): int
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        return $quantity;
    }
}
