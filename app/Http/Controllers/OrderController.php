<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a paginated, filterable listing of all customer orders.
     *
     * Defaults to showing "pending" orders when no status filter has
     * been explicitly chosen by the admin.
     */
    public function index(Request $request): View
    {
        $status = $request->has('status') ? $request->string('status')->toString() : 'pending';

        $orders = Order::query()
            ->withCount('items')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->orderStatus($status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'statusFilter' => $status,
        ]);
    }

    /**
     * Return the full order details (customer info + line items) as
     * JSON, used to populate the "Order Details" modal.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load('items');

        return response()->json([
            'order_number' => $order->order_number,
            'status_label' => $order->status_label,
            'display_status' => $order->display_status,
            'date' => $order->created_at->format('d M Y, h:i A'),
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email,
            'shipping_address' => $order->shipping_address,
            'city' => $order->city,
            'payment_method' => strtoupper($order->payment_method),
            'notes' => $order->notes,
            'subtotal' => number_format((float) $order->subtotal, 2),
            'total' => number_format((float) $order->total, 2),
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'variant' => $item->variant_label,
                'quantity' => $item->quantity,
                'price' => number_format((float) $item->price, 2),
                'line_total' => number_format((float) $item->line_total, 2),
            ]),
        ]);
    }

    /**
     * Cancel the specified order and return its items' stock.
     *
     * Only orders that are still "pending" or "processing" (i.e. not
     * yet completed or refunded) may be cancelled.
     */
    public function cancel(Order $order): RedirectResponse
    {
        if (! $order->canBeCancelled()) {
            return redirect()
                ->route('orders.index')
                ->with('error', "Order \"{$order->order_number}\" can no longer be cancelled.");
        }

        DB::transaction(function () use ($order) {
            $this->restockItems($order);

            $order->update([
                'status' => 'cancelled',
                'cancelled_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('orders.index')
            ->with('success', "Order \"{$order->order_number}\" has been cancelled and stock restored.");
    }

    /**
     * Refund the specified order and return its items' stock.
     *
     * Only "completed" orders that haven't already been refunded may
     * be refunded.
     */
    public function refund(Order $order): RedirectResponse
    {
        if (! $order->canBeRefunded()) {
            return redirect()
                ->route('orders.index')
                ->with('error', "Order \"{$order->order_number}\" is not eligible for a refund.");
        }

        DB::transaction(function () use ($order) {
            $this->restockItems($order);

            $order->update([
                'refunded_at' => now(),
                'refunded_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('orders.index')
            ->with('success', "Order \"{$order->order_number}\" has been marked as refunded and stock restored.");
    }

    /**
     * Return the stock quantity of every item on the order back to
     * its product/variant.
     */
    private function restockItems(Order $order): void
    {
        $order->load('items.product', 'items.variant');

        foreach ($order->items as $item) {
            if ($item->variant) {
                $item->variant->increment('stock_quantity', $item->quantity);
            } elseif ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }
    }
}
