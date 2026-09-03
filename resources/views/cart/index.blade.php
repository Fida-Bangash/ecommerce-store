@extends('layouts.front')

@section('title', 'Your Cart')
@section('meta_description', 'Review the items in your shopping cart.')

@section('content')
<div class="container py-5">

    <h1 class="mb-4">Your Cart</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($cart->isEmpty())

        <div class="text-center py-5">
            <p class="mb-3">Your cart is empty.</p>
            <a href="{{ route('home') }}" class="btn btn-outline-dark">Continue Shopping</a>
        </div>

    @else

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th style="width: 160px;">Quantity</th>
                        <th>Line Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cart->items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($item->product->primary_image_url)
                                        <img src="{{ $item->product->primary_image_url }}"
                                             alt="{{ $item->product->name }}"
                                             width="56" height="56"
                                             style="object-fit: cover; border-radius: 6px;">
                                    @endif
                                    <span>{{ $item->product->name }}</span>
                                </div>
                            </td>

                            <td>Rs {{ number_format($item->price, 2) }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <form action="{{ route('cart.update', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="decrement">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Decrease quantity">
                                            <svg width="14" height="14"><use href="#minus"></use></svg>
                                        </button>
                                    </form>

                                    <span>{{ $item->quantity }}</span>

                                    <form action="{{ route('cart.update', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="increment">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Increase quantity">
                                            <svg width="14" height="14"><use href="#plus"></use></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                            <td>Rs {{ number_format($item->line_total, 2) }}</td>

                            <td>
                                <form action="{{ route('cart.destroy', $item) }}" method="POST"
                                      class="js-confirm-form" data-confirm-message="Remove this item from your cart?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Remove item">
                                        <svg width="14" height="14"><use href="#trash"></use></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            <div style="min-width: 280px;">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>Rs {{ number_format($cart->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2">
                    <span>Total</span>
                    <span>Rs {{ number_format($cart->total, 2) }}</span>
                </div>

                <a href="{{ route('checkout.index') }}" class="btn btn-dark w-100 mt-3">Proceed to Checkout</a>

                <form action="{{ route('cart.clear') }}" method="POST" class="mt-2 js-confirm-form"
                      data-confirm-message="Clear the entire cart?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link w-100 text-muted">Clear Cart</button>
                </form>
            </div>
        </div>

    @endif

</div>
@endsection
