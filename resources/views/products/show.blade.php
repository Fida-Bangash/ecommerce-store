@extends('layouts.front')

@section('title', $product->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 155))

@section('content')
@php
    $discountPercent = $product->discount_price
        ? round((($product->regular_price - $product->discount_price) / $product->regular_price) * 100)
        : null;

    $variantsForJs = $product->variants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'size' => $variant->size,
            'color' => $variant->color,
            'stock_quantity' => $variant->stock_quantity,
            'extra_price' => (float) $variant->extra_price,
        ];
    })->values()->all();

    $basePriceForJs = (float) $product->effective_price;
@endphp

<div class="container py-5">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}" class="text-decoration-none text-muted">Shop</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop', ['category' => $product->category_id]) }}" class="text-decoration-none text-muted">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active text-dark" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-5">

        {{-- Image Gallery --}}
        <div class="col-lg-6">
            <div class="product-gallery-main rounded-4 overflow-hidden mb-3 position-relative" style="background:#f8f9fa; height: 460px;">
                @if ($discountPercent)
                    <span class="badge bg-danger position-absolute m-3" style="z-index: 2;">-{{ $discountPercent }}%</span>
                @endif

                @forelse ($product->images as $index => $image)
                    <img src="{{ $image->image_url }}" alt="{{ $product->name }}"
                         class="gallery-main-img w-100 h-100 {{ $index === 0 ? '' : 'd-none' }}"
                         data-gallery-img="{{ $index }}"
                         style="object-fit: cover;">
                @empty
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                        No Image Available
                    </div>
                @endforelse
            </div>

            @if ($product->images->count() > 1)
                <div class="d-flex gap-2 flex-wrap">
                    @foreach ($product->images as $index => $image)
                        <button type="button"
                                class="gallery-thumb border-0 p-0 rounded-3 overflow-hidden {{ $index === 0 ? 'active' : '' }}"
                                data-gallery-thumb="{{ $index }}"
                                style="width: 76px; height: 76px;">
                            <img src="{{ $image->image_url }}" alt="{{ $product->name }} thumbnail" class="w-100 h-100" style="object-fit: cover;">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Product Info --}}
        <div class="col-lg-6">
            <span class="text-uppercase text-muted small" style="letter-spacing:.05em;">{{ $product->category->name }}</span>
            <h1 class="fs-2 fw-bold mt-1 mb-3">{{ $product->name }}</h1>

            <div class="d-flex align-items-baseline gap-3 mb-3">
                <span class="fs-3 fw-bold text-dark" id="product-price">
                    Rs {{ number_format($product->effective_price, 2) }}
                </span>
                @if ($product->discount_price)
                    <del class="text-muted fs-5">Rs {{ number_format($product->regular_price, 2) }}</del>
                @endif
            </div>

            <div class="mb-4">
                @if ($product->stock_status === 'out_of_stock')
                    <span class="badge bg-secondary">Out of Stock</span>
                @elseif ($product->stock_status === 'low_stock')
                    <span class="badge bg-warning text-dark">Only {{ $product->stock_quantity }} left</span>
                @else
                    <span class="badge bg-success">In Stock</span>
                @endif
            </div>

            @if ($product->description)
                <p class="text-muted mb-4">{{ $product->description }}</p>
            @endif

            <form action="{{ route('cart.store') }}" method="POST" id="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" id="variant_id_input" value="">

                @if ($product->has_variants)
                    @if (count($product->available_sizes))
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Size</label>
                            <div class="d-flex flex-wrap gap-2" id="size-options">
                                @foreach ($product->available_sizes as $size)
                                    <button type="button" class="btn btn-outline-dark btn-sm variant-option" data-size="{{ $size }}">
                                        {{ $size }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (count($product->available_colors))
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Color</label>
                            <div class="d-flex flex-wrap gap-2" id="color-options">
                                @foreach ($product->available_colors as $color)
                                    <button type="button"
                                            class="btn btn-outline-dark btn-sm variant-option d-inline-flex align-items-center gap-2"
                                            data-color="{{ $color['name'] }}">
                                        @if ($color['hex'])
                                            <span class="d-inline-block rounded-circle" style="width:14px;height:14px;background:{{ $color['hex'] }};border:1px solid #ccc;"></span>
                                        @endif
                                        {{ $color['name'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="small text-muted mb-3" id="variant-feedback">Please select an option above.</div>
                @endif

                <div class="d-flex align-items-center gap-3 mb-4">
                    <label for="quantity" class="form-label fw-semibold mb-0">Qty</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1"
                           max="{{ $product->stock_quantity ?: 1 }}" class="form-control" style="width: 90px;">
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="buy_now" value="0" id="add-to-cart-btn"
                            class="btn btn-outline-dark btn-lg rounded-pill px-4 d-inline-flex align-items-center gap-2"
                            {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                        <svg width="18" height="18"><use href="#cart"></use></svg>
                        {{ $product->stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart' }}
                    </button>

                    <button type="submit" name="buy_now" value="1" id="buy-now-btn"
                            class="btn btn-dark btn-lg rounded-pill px-4"
                            {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                        Buy Now
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Related Products --}}
    @if ($relatedProducts->isNotEmpty())
        <div class="mt-5 pt-5 border-top">
            <h3 class="fs-4 fw-bold mb-4">You may also like</h3>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                @foreach ($relatedProducts as $related)
                    <div class="col">
                        @include('partials.product-card', ['product' => $related])
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    (function () {
        // Gallery: swap main image on thumbnail click.
        document.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
            thumb.addEventListener('click', function () {
                const index = thumb.dataset.galleryThumb;

                document.querySelectorAll('[data-gallery-img]').forEach((img) => {
                    img.classList.toggle('d-none', img.dataset.galleryImg !== index);
                });

                document.querySelectorAll('[data-gallery-thumb]').forEach((t) => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });

        const form = document.getElementById('add-to-cart-form');
        if (!form) {
            return;
        }

        const hasVariants = @json($product->has_variants);
        if (!hasVariants) {
            return;
        }

        const variants = @json($variantsForJs);
        const basePrice = @json($basePriceForJs);
        const hasSizes = document.getElementById('size-options') !== null;
        const hasColors = document.getElementById('color-options') !== null;

        const variantIdInput = document.getElementById('variant_id_input');
        const priceEl = document.getElementById('product-price');
        const feedbackEl = document.getElementById('variant-feedback');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const buyNowBtn = document.getElementById('buy-now-btn');
        const quantityInput = document.getElementById('quantity');

        let selectedSize = null;
        let selectedColor = null;

        function selectOption(group, button, value, type) {
            group.querySelectorAll('.variant-option').forEach((btn) => {
                btn.classList.remove('btn-dark', 'text-white');
                btn.classList.add('btn-outline-dark');
            });

            button.classList.remove('btn-outline-dark');
            button.classList.add('btn-dark', 'text-white');

            if (type === 'size') {
                selectedSize = value;
            } else {
                selectedColor = value;
            }

            updateSelection();
        }

        document.querySelectorAll('#size-options .variant-option').forEach((btn) => {
            btn.addEventListener('click', function () {
                selectOption(document.getElementById('size-options'), btn, btn.dataset.size, 'size');
            });
        });

        document.querySelectorAll('#color-options .variant-option').forEach((btn) => {
            btn.addEventListener('click', function () {
                selectOption(document.getElementById('color-options'), btn, btn.dataset.color, 'color');
            });
        });

        function updateSelection() {
            if (hasSizes && !selectedSize) {
                feedbackEl.textContent = 'Please select a size.';
                disableAddToCart();
                return;
            }

            if (hasColors && !selectedColor) {
                feedbackEl.textContent = 'Please select a color.';
                disableAddToCart();
                return;
            }

            const match = variants.find((v) =>
                (!hasSizes || v.size === selectedSize) &&
                (!hasColors || v.color === selectedColor)
            );

            if (!match) {
                feedbackEl.textContent = 'This combination is not available.';
                disableAddToCart();
                return;
            }

            variantIdInput.value = match.id;
            priceEl.textContent = 'Rs ' + (basePrice + match.extra_price).toFixed(2);
            quantityInput.max = match.stock_quantity || 1;

            if (match.stock_quantity <= 0) {
                feedbackEl.textContent = 'This combination is out of stock.';
                disableAddToCart();
                return;
            }

            feedbackEl.textContent = match.stock_quantity + ' in stock.';
            enableAddToCart();
        }

        function disableAddToCart() {
            addToCartBtn.disabled = true;
            buyNowBtn.disabled = true;
            variantIdInput.value = '';
        }

        function enableAddToCart() {
            addToCartBtn.disabled = false;
            buyNowBtn.disabled = false;
        }

        // Require a selection before the form can be submitted.
        disableAddToCart();
    })();
</script>
@endpush

@push('styles')
<style>
    .gallery-thumb {
        opacity: .6;
        transition: opacity .2s ease;
        border: 2px solid transparent !important;
    }
    .gallery-thumb.active,
    .gallery-thumb:hover {
        opacity: 1;
        border-color: #212529 !important;
    }
    .variant-option.btn-dark {
        border-color: #212529;
    }
</style>
@endpush
