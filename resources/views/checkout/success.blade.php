@extends('layouts.front')

@section('title', 'Order Confirmed')
@section('meta_description', 'Your order has been placed successfully.')

@section('content')
<div class="container py-5">

    <div class="text-center mb-5">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success mb-3"
             style="width: 72px; height: 72px;">
            <svg width="32" height="32" style="filter: invert(1);"><use href="#check"></use></svg>
        </div>
        <h1 class="fs-2 fw-bold mb-2">Thank you, {{ $order->customer_name }}!</h1>
        <p class="text-muted">Your order has been placed successfully.</p>
        <p class="fw-semibold">Order Number: {{ $order->order_number }}</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-4 border-light shadow-sm mb-4">
                <h5 class="mb-3">Order Items</h5>

                @foreach ($order->items as $item)
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <div class="fw-semibold">{{ $item->product_name }}</div>
                            @if ($item->variant_label)
                                <div class="small text-muted">{{ $item->variant_label }}</div>
                            @endif
                            <div class="small text-muted">Qty: {{ $item->quantity }}</div>
                        </div>
                        <div class="fw-semibold">Rs {{ number_format($item->line_total, 2) }}</div>
                    </div>
                @endforeach

                <div class="border-top pt-3 mt-2 d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span>Rs {{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <div class="card p-4 border-light shadow-sm mb-4">
                <h5 class="mb-3">Shipping To</h5>
                <p class="mb-1">{{ $order->customer_name }}</p>
                <p class="mb-1">{{ $order->customer_phone }}</p>
                <p class="mb-1">{{ $order->shipping_address }}, {{ $order->city }}</p>
                <p class="mb-0 text-muted small">
                    Payment: {{ $order->payment_method === 'cod' ? 'Cash on Delivery' : 'Bank Transfer' }}
                </p>
            </div>

            <div class="text-center">
                <a href="{{ route('shop') }}" class="btn btn-dark rounded-pill px-4">Continue Shopping</a>
            </div>
        </div>
    </div>

</div>
@endsection
