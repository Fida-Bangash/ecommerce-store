@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $categories */
    $categories ??= \App\Models\Category::query()->active()->orderBy('name')->get();
    $cartItemCount ??= 0;
@endphp

<header>
    <div class="container-fluid">
        <div class="row py-3 border-bottom">

            <div class="col-sm-4 col-lg-3 text-center text-sm-start">
                <div class="main-logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('frontend/images/logo.png') }}" alt="{{ config('app.name') }}" class="img-fluid">
                    </a>
                </div>
            </div>

            <div class="col-sm-6 offset-sm-2 offset-md-0 col-lg-5 d-none d-lg-block">
                <form action="{{ route('shop') }}" method="GET" class="search-bar row bg-light p-2 my-2 rounded-4">
                    <div class="col-md-4 d-none d-md-block">
                        <select name="category" class="form-select border-0 bg-transparent">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-11 col-md-7">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control border-0 bg-transparent"
                               placeholder="Search products">
                    </div>
                    <button type="submit" class="col-1 border-0 bg-transparent">
                        <svg width="24" height="24" viewBox="0 0 24 24">
                            <use xlink:href="#search"></use>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="col-sm-8 col-lg-4 d-flex justify-content-end gap-3 align-items-center mt-4 mt-sm-0 justify-content-center justify-content-sm-end">
                <ul class="d-flex justify-content-end list-unstyled m-0 align-items-center">
                    @auth
                        <li>
                            <a href="{{ route('dashboard') }}" class="rounded-circle bg-light p-2 mx-1" title="Dashboard">
                                <svg width="24" height="24" viewBox="0 0 24 24">
                                    <use xlink:href="#user"></use>
                                </svg>
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="rounded-circle bg-light p-2 mx-1" title="Login">
                                <svg width="24" height="24" viewBox="0 0 24 24">
                                    <use xlink:href="#user"></use>
                                </svg>
                            </a>
                        </li>
                    @endauth

                    <li>
                        <a href="{{ route('cart.index') }}" class="rounded-circle bg-light p-2 mx-1 position-relative" title="Cart">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <use xlink:href="#cart"></use>
                            </svg>
                            @if ($cartItemCount > 0)
                                <span class="badge bg-dark rounded-pill position-absolute top-0 start-100 translate-middle">
                                    {{ $cartItemCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <div class="container-fluid">
        <div class="row py-3">
            <div class="d-flex justify-content-center justify-content-sm-between align-items-center">
                <nav class="main-menu d-flex navbar navbar-expand-lg">

                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">

                        <div class="offcanvas-header justify-content-center">
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>

                        <div class="offcanvas-body">
                            <ul class="navbar-nav justify-content-end menu-list list-unstyled d-flex gap-md-4 mb-0 text-black text-uppercase fw-bold">
                                <li class="nav-item">
                                    <a href="{{ route('home') }}" class="nav-link @if(request()->routeIs('home')) active @endif">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('shop') }}" class="nav-link @if(request()->routeIs('shop')) active @endif">Shop</a>
                                </li>
                                @foreach ($categories as $category)
                                    <li class="nav-item">
                                        <a href="{{ route('shop', ['category' => $category->id]) }}" class="nav-link">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>

                </nav>
            </div>
        </div>
    </div>
</header>
