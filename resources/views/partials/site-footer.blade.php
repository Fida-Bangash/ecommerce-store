@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $categories */
    $categories ??= \App\Models\Category::query()->active()->orderBy('name')->get();
@endphp

<footer class="py-5 border-top mt-5">
    <div class="container">
        <div class="row gy-4">

            <div class="col-md-4">
                <h5 class="widget-title mb-3">{{ config('app.name') }}</h5>
                <p class="text-muted">
                    Quality products, delivered to your door.
                </p>
            </div>

            <div class="col-md-4">
                <h5 class="widget-title mb-3">Shop</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('shop') }}" class="text-decoration-none text-muted">All Products</a></li>
                    @foreach ($categories as $category)
                        <li class="mb-2">
                            <a href="{{ route('shop', ['category' => $category->id]) }}" class="text-decoration-none text-muted">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="col-md-4">
                <h5 class="widget-title mb-3">Account</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('cart.index') }}" class="text-decoration-none text-muted">Your Cart</a></li>
                    @auth
                        <li class="mb-2"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    @else
                        <li class="mb-2"><a href="{{ route('login') }}" class="text-decoration-none text-muted">Login</a></li>
                    @endauth
                </ul>
            </div>

        </div>

        <div class="text-center text-muted small mt-5">
            &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</footer>
