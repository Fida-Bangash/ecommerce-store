{{-- Usage: @include('partials.product-card', ['product' => $product]) --}}
@php
    $discountPercent = $product->discount_price
        ? round((($product->regular_price - $product->discount_price) / $product->regular_price) * 100)
        : null;
@endphp

<div class="product-card position-relative h-100 bg-white rounded-4 overflow-hidden shadow-sm">

    @if ($discountPercent)
        <span class="badge bg-danger position-absolute m-3" style="z-index: 2;">-{{ $discountPercent }}%</span>
    @elseif ($product->stock_status === 'out_of_stock')
        <span class="badge bg-secondary position-absolute m-3" style="z-index: 2;">Sold Out</span>
    @elseif ($product->stock_status === 'low_stock')
        <span class="badge bg-warning text-dark position-absolute m-3" style="z-index: 2;">Low Stock</span>
    @endif

    <a href="{{ route('product.show', $product) }}" class="d-block product-card-image overflow-hidden text-decoration-none" style="height: 220px; background: #f8f9fa;">
        @if ($product->primary_image_url)
            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}"
                 class="w-100 h-100 product-card-img" style="object-fit: cover; transition: transform .35s ease;">
        @else
            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                No Image
            </div>
        @endif
    </a>

    <div class="p-3 d-flex flex-column">
        <span class="text-uppercase text-muted small mb-1" style="letter-spacing: .04em;">
            {{ $product->category->name }}
        </span>

        <a href="{{ route('product.show', $product) }}" class="text-decoration-none text-dark">
            <h5 class="fs-6 fw-semibold mb-2 text-truncate">{{ $product->name }}</h5>
        </a>

        <div class="d-flex align-items-baseline gap-2 mb-3">
            @if ($product->discount_price)
                <span class="fw-bold fs-5 text-dark">Rs {{ number_format($product->discount_price, 2) }}</span>
                <del class="text-muted small">Rs {{ number_format($product->regular_price, 2) }}</del>
            @else
                <span class="fw-bold fs-5 text-dark">Rs {{ number_format($product->regular_price, 2) }}</span>
            @endif
        </div>

        <div class="mt-auto">
            @if ($product->has_variants)
                <a href="{{ route('product.show', $product) }}" class="btn btn-dark w-100 rounded-pill">
                    Select Options
                </a>
            @else
                @include('partials.add-to-cart-button', ['product' => $product])
            @endif
        </div>
    </div>
</div>

<style>
    .product-card {
        transition: box-shadow .25s ease, transform .25s ease;
        border: 1px solid #eee;
    }
    .product-card:hover {
        box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.08);
        transform: translateY(-4px);
    }
    .product-card:hover .product-card-img {
        transform: scale(1.06);
    }
</style>
