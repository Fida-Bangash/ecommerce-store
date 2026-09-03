<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * Display the current customer's cart.
     */
    public function index(): View
    {
        $cart = $this->cartService->currentCart();
        $cart->load(['items.product.images']);

        return view('cart.index', compact('cart'));
    }

    /**
     * Add a product to the cart.
     */
    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $product = Product::query()->with('variants')->findOrFail($request->integer('product_id'));

        $variant = $request->filled('variant_id')
            ? $product->variants->firstWhere('id', $request->integer('variant_id'))
            : null;

        if ($product->has_variants && ! $variant) {
            return back()->with('error', 'Please select a size/color option.');
        }

        $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;

        if ($availableStock <= 0) {
            return back()->with('error', 'This product is out of stock.');
        }

        $this->cartService->addItem($product, $request->integer('quantity', 1), $variant);

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return back()->with('success', "{$product->name} added to cart.");
    }

    /**
     * Update a cart item's quantity (explicit value, or +/- one step).
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        match ($request->input('action')) {
            'increment' => $this->cartService->increment($cartItem),
            'decrement' => $this->cartService->decrement($cartItem),
            default => $this->cartService->setQuantity($cartItem, $request->integer('quantity', $cartItem->quantity)),
        };

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $this->cartService->removeItem($cartItem);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Empty the current customer's cart entirely.
     */
    public function clear(): RedirectResponse
    {
        $this->cartService->clear($this->cartService->currentCart());

        return back()->with('success', 'Cart cleared.');
    }

    /**
     * Ensure the cart item being modified actually belongs to the
     * requesting customer's own cart (guest or logged-in).
     */
    private function authorizeCartItem(Request $request, CartItem $cartItem): void
    {
        $currentCartId = $this->cartService->currentCart()->id;

        abort_unless($cartItem->cart_id === $currentCartId, 403);
    }
}
