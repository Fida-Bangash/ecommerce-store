<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status_label' => $order->status_label,
            'display_status' => $order->display_status,
            'can_refund_items' => $order->canRefundItems(),
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
                'id' => $item->id,
                'name' => $item->product_name,
                'variant' => $item->variant_label,
                'quantity' => $item->quantity,
                'price' => number_format((float) $item->price, 2),
                'line_total' => number_format((float) $item->line_total, 2),
                'refunded_quantity' => $item->refunded_quantity,
                'remaining_quantity' => $item->remainingQuantity(),
            ]),
        ]);
    }

    /**
     * Update the status of the specified order.
     *
     * Handles every transition through one endpoint: simple forward
     * moves (processing/dispatched/completed) just update the status
     * column, while "cancelled" and "refunded" also restock the
     * order's items and record which admin made the change.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $allowed = $order->availableStatusOptions();

        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys($allowed))],
        ]);

        $newStatus = $data['status'];

        if (! array_key_exists($newStatus, $allowed)) {
            return redirect()
                ->route('orders.index')
                ->with('error', "Order \"{$order->order_number}\" can't be moved to that status.");
        }

        DB::transaction(function () use ($order, $newStatus) {
            if ($newStatus === 'cancelled') {
                $this->restockItems($order);
                $order->update([
                    'status' => 'cancelled',
                    'cancelled_by' => Auth::id(),
                ]);

                return;
            }

            if ($newStatus === 'refunded') {
                $this->restockItems($order);

                foreach ($order->items as $item) {
                    $item->update(['refunded_quantity' => $item->quantity]);
                }

                $order->update([
                    'refunded_at' => now(),
                    'refunded_by' => Auth::id(),
                ]);

                return;
            }

            $order->update(['status' => $newStatus]);
        });

        $label = $allowed[$newStatus];

        return redirect()
            ->route('orders.index')
            ->with('success', "Order \"{$order->order_number}\" has been marked as {$label}.");
    }

    /**
     * Refund a specific quantity of a single line item on an order,
     * instead of refunding the whole order. Restocks only the units
     * being refunded, and automatically marks the whole order as
     * refunded once every line item has been fully refunded.
     */
    public function refundItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        if ($item->order_id !== $order->id) {
            abort(404);
        }

        if (! $order->canRefundItems()) {
            return redirect()
                ->route('orders.index')
                ->with('error', "Order \"{$order->order_number}\" isn't eligible for a refund.");
        }

        $remaining = $item->remainingQuantity();

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.max($remaining, 1)],
        ]);

        if ($remaining < 1) {
            return redirect()
                ->route('orders.index')
                ->with('error', "\"{$item->product_name}\" has already been fully refunded.");
        }

        $quantity = $data['quantity'];

        DB::transaction(function () use ($order, $item, $quantity) {
            $item->load('product', 'variant');
            $this->restockQuantity($item, $quantity);
            $item->increment('refunded_quantity', $quantity);

            // Once every line item on the order has been fully
            // refunded, treat the order itself as refunded.
            if ($order->allItemsFullyRefunded()) {
                $order->update([
                    'refunded_at' => now(),
                    'refunded_by' => Auth::id(),
                ]);
            }
        });

        return redirect()
            ->route('orders.index')
            ->with('success', "Refunded {$quantity} x \"{$item->product_name}\" on order \"{$order->order_number}\".");
    }

    /**
     * Return the stock quantity of every item on the order back to
     * its product/variant.
     */
    private function restockItems(Order $order): void
    {
        $order->load('items.product', 'items.variant');

        foreach ($order->items as $item) {
            $this->restockQuantity($item, $item->quantity);
        }
    }

    /**
     * Add the given quantity back to a line item's product/variant
     * stock. Assumes the item's "product" and "variant" relations
     * are already loaded.
     */
    private function restockQuantity(OrderItem $item, int $quantity): void
    {
        if ($item->variant) {
            $item->variant->increment('stock_quantity', $quantity);
        } elseif ($item->product) {
            $item->product->increment('stock_quantity', $quantity);
        }
    }
}
