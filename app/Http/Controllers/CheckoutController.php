<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * Show the checkout page with a summary of the current cart and
     * the shipping/payment details form.
     */
    public function index(): View|RedirectResponse
    {
        $cart = $this->cartService->currentCart();
        $cart->load(['items.product.images', 'items.variant']);

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty. Add something before checking out.');
        }

        foreach ($cart->items as $item) {
            $available = $item->variant ? $item->variant->stock_quantity : $item->product->stock_quantity;

            if ($available < $item->quantity) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', "\"{$item->product->name}\" no longer has enough stock. Please update your cart.");
            }
        }

        return view('checkout.index', compact('cart'));
    }

    /**
     * Place the order: snapshot the cart into an order, deduct stock,
     * and empty the cart.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $cart = $this->cartService->currentCart();
        $cart->load(['items.product', 'items.variant']);

        if ($cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty. Add something before checking out.');
        }

        foreach ($cart->items as $item) {
            $available = $item->variant ? $item->variant->stock_quantity : $item->product->stock_quantity;

            if ($available < $item->quantity) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', "\"{$item->product->name}\" no longer has enough stock. Please update your cart.");
            }
        }

        $order = DB::transaction(function () use ($cart, $request) {
            $validated = $request->validated();

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => Auth::id(),
                'session_id' => Auth::check() ? null : Session::getId(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'shipping_address' => $validated['shipping_address'],
                'city' => $validated['city'],
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'subtotal' => $cart->subtotal,
                'total' => $cart->total,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->name,
                    'variant_label' => $item->variant?->label,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                ]);

                if ($item->variant) {
                    $item->variant->decrement('stock_quantity', $item->quantity);
                } else {
                    $item->product->decrement('stock_quantity', $item->quantity);
                }
            }

            $this->cartService->clear($cart);

            return $order;
        });

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Your order has been placed successfully.');
    }

    /**
     * Show the order confirmation page.
     */
    public function success(Order $order): View
    {
        $order->load('items');

        return view('checkout.success', compact('order'));
    }
}
