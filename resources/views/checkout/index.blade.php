@extends('layouts.front')

@section('title', 'Checkout')
@section('meta_description', 'Complete your order.')

@section('content')
<div class="container py-5">

    <h1 class="mb-4">Checkout</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-5">

        {{-- Shipping & Payment Form --}}
        <div class="col-lg-7">
            <form action="{{ route('checkout.store') }}" method="POST">
                @csrf

                <div class="card p-4 border-light shadow-sm mb-4">
                    <h5 class="mb-3">Shipping Details</h5>

                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Full Name</label>
                        <input type="text" name="customer_name" id="customer_name"
                               class="form-control @error('customer_name') is-invalid @enderror"
                               value="{{ old('customer_name') }}" required>
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_phone" class="form-label">Phone Number</label>
                            <input type="text" name="customer_phone" id="customer_phone"
                                   class="form-control @error('customer_phone') is-invalid @enderror"
                                   value="{{ old('customer_phone') }}" required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="customer_email" class="form-label">Email (optional)</label>
                            <input type="email" name="customer_email" id="customer_email"
                                   class="form-control @error('customer_email') is-invalid @enderror"
                                   value="{{ old('customer_email') }}">
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="shipping_address" class="form-label">Shipping Address</label>
                        <textarea name="shipping_address" id="shipping_address" rows="3"
                                  class="form-control @error('shipping_address') is-invalid @enderror"
                                  required>{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" name="city" id="city"
                               class="form-control @error('city') is-invalid @enderror"
                               value="{{ old('city') }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="notes" class="form-label">Order Notes (optional)</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="card p-4 border-light shadow-sm mb-4">
                    <h5 class="mb-3">Payment Method</h5>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_cod"
                               value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                        <label class="form-check-label" for="payment_cod">Cash on Delivery</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_bank"
                               value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="payment_bank">Bank Transfer</label>
                    </div>

                    @error('payment_method')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-dark btn-lg w-100 rounded-pill">Place Order</button>
            </form>
        </div>

        {{-- Order Summary --}}
        <div class="col-lg-5">
            <div class="card p-4 border-light shadow-sm">
                <h5 class="mb-3">Order Summary</h5>

                @foreach ($cart->items as $item)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if ($item->product->primary_image_url)
                            <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product->name }}"
                                 width="56" height="56" style="object-fit: cover; border-radius: 6px;">
                        @endif

                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $item->product->name }}</div>
                            @if ($item->variant)
                                <div class="small text-muted">{{ $item->variant->label }}</div>
                            @endif
                            <div class="small text-muted">Qty: {{ $item->quantity }}</div>
                        </div>

                        <div class="fw-semibold">Rs {{ number_format($item->line_total, 2) }}</div>
                    </div>
                @endforeach

                <div class="border-top pt-3 mt-2">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>Rs {{ number_format($cart->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span>Rs {{ number_format($cart->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
