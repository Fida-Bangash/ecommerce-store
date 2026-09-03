<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

  {{-- SEO Optimization --}}
  <meta name="description" content="@yield('meta_description', config('app.name'))">
  <meta name="author" content="{{ config('app.name') }}">

  {{-- Favicon --}}
  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.ico') }}">

  {{-- Local Third-Party Libraries (100% Offline Compatible) --}}
  <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/apexcharts/apexcharts.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

  {{-- Main Design System & Custom Stylesheet --}}
  <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

  @stack('styles')
</head>

<body>

  @include('partials.sidebar')

  {{-- ==========================================
         START: Main Content Area
         ========================================== --}}
  <div class="main-wrapper">

    @include('partials.topbar')

    @hasSection('page_header')
      {{-- START: Page Header Banner --}}
      <div class="page-header">
        <div>
          <h1 class="page-title">@yield('page_title')</h1>
          <p class="page-subtitle">@yield('page_subtitle')</p>
        </div>
        @yield('page_header_actions')
      </div>
      {{-- END: Page Header Banner --}}
    @endif

    {{-- START: Page Content --}}
    @yield('content')
    {{-- END: Page Content --}}

    @include('partials.footer')

  </div>
  {{-- ==========================================
         END: Main Content Area
         ========================================== --}}

  {{-- Local Third-Party Libraries Script dependencies --}}
  <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

  {{-- Local dashboard interactions controller --}}
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>

  @stack('scripts')
</body>

</html>
