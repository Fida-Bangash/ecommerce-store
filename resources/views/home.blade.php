@extends('layouts.front')

@section('title', config('app.name') . ' - Home')
@section('meta_description', config('app.name') . ' online store')

@section('content')

    <div class="preloader-wrapper">
        <div class="preloader"></div>
    </div>

    {{-- Hero --}}
    <section class="py-0">
        <div class="position-relative" style="background: linear-gradient(135deg, #1f2937, #374151);">
            <svg viewBox="0 0 1600 480" xmlns="http://www.w3.org/2000/svg" class="d-block w-100" style="max-height: 480px;">
                <defs>
                    <linearGradient id="heroBg" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#1f2937"/>
                        <stop offset="100%" stop-color="#374151"/>
                    </linearGradient>
                </defs>
                <rect width="1600" height="480" fill="url(#heroBg)"/>

                {{-- Decorative shopping bags (generic, not a specific product) --}}
                <g opacity="0.9" transform="translate(1120,120)">
                    <rect x="0" y="60" width="160" height="180" rx="10" fill="#f59e0b"/>
                    <path d="M30 60 v-25 a50 50 0 0 1 100 0 v25" fill="none" stroke="#fff" stroke-width="10"/>
                </g>
                <g opacity="0.75" transform="translate(1280,180)">
                    <rect x="0" y="40" width="130" height="150" rx="10" fill="#ef4444"/>
                    <path d="M25 40 v-20 a40 40 0 0 1 80 0 v20" fill="none" stroke="#fff" stroke-width="8"/>
                </g>
                <g opacity="0.6" transform="translate(1010,220)">
                    <rect x="0" y="30" width="110" height="120" rx="8" fill="#10b981"/>
                    <path d="M20 30 v-16 a35 35 0 0 1 70 0 v16" fill="none" stroke="#fff" stroke-width="7"/>
                </g>
            </svg>

            <div class="position-absolute top-50 start-0 translate-middle-y text-white px-4 px-md-5"
                 style="max-width: 600px;">
                <h1 class="display-5 mb-3 text-white">Welcome to Spark</h1>
                <p class="lead mb-4">Discover our full collection of quality products.</p>
                <a href="{{ route('shop') }}" class="btn btn-light btn-lg px-4">Shop Now</a>
            </div>
        </div>
    </section>

    {{-- Trust badges --}}
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <svg width="40" height="40"><use xlink:href="#cart"></use></svg>
                        </div>
                        <div class="col-10">
                            <h4 class="element-title mb-1">Easy Ordering</h4>
                            <p class="text-muted mb-0">Add to cart and check out in a few clicks.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <svg width="40" height="40"><use xlink:href="#gift"></use></svg>
                        </div>
                        <div class="col-10">
                            <h4 class="element-title mb-1">Quality Products</h4>
                            <p class="text-muted mb-0">Carefully selected items you can trust.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <svg width="40" height="40"><use xlink:href="#love"></use></svg>
                        </div>
                        <div class="col-10">
                            <h4 class="element-title mb-1">Customer Support</h4>
                            <p class="text-muted mb-0">Here to help whenever you need us.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    @if ($categories->isNotEmpty())
        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="section-title mb-4">Shop by Category</h2>
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    @foreach ($categories as $category)
                        <div class="col">
                            <a href="{{ route('shop', ['category' => $category->id]) }}"
                               class="d-block text-center text-decoration-none text-dark p-3 bg-white rounded shadow-sm h-100">
                                @if ($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                                         class="img-fluid mb-2" style="height: 100px; object-fit: cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center mb-2" style="height: 100px;">
                                        <svg width="40" height="40"><use xlink:href="#category"></use></svg>
                                    </div>
                                @endif
                                <div class="fw-semibold">{{ $category->name }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Trending products --}}
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">Trending Products</h2>
                <a href="{{ route('shop') }}" class="text-decoration-none">View all &rarr;</a>
            </div>

            @if ($trendingProducts->isEmpty())
                <p class="text-muted">No products available yet.</p>
            @else
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    @foreach ($trendingProducts as $product)
                        <div class="col">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

@endsection
