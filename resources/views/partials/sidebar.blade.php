{{-- ==========================================
     Sidebar Component (Spark Admin)
     ========================================== --}}
<div class="sidebar-wrapper" id="sidebar">
  {{-- Brand Logo / Identity --}}
  <a href="{{ route('dashboard') }}" class="sidebar-brand">
    <i class="bi bi-asterisk"></i>
    <span>{{ config('app.name') }}</span>
  </a>

  {{-- Navigation Menu --}}
  <div class="flex-grow-1 overflow-y-auto">
    {{-- Group: Menu --}}
    <div class="sidebar-menu-section">
      <div class="sidebar-menu-title">Menu</div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-item">
          <a href="{{ route('dashboard') }}"
             class="sidebar-menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
             id="menu-overview" title="Overview">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
          </a>
        </li>
      </ul>
    </div>

    {{-- Group: Catalog --}}
    <div class="sidebar-menu-section">
      <div class="sidebar-menu-title">Catalog</div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-item">
          <a href="{{ route('categories.index') }}"
             class="sidebar-menu-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
             id="menu-categories" title="Category Management">
            <i class="bi bi-tags-fill"></i>
            <span>Categories</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="{{ route('products.index') }}"
             class="sidebar-menu-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
             id="menu-products" title="Product Management">
            <i class="bi bi-box-seam-fill"></i>
            <span>Products</span>
          </a>
        </li>
      </ul>
    </div>

    {{-- Group: Sales --}}
    <div class="sidebar-menu-section">
      <div class="sidebar-menu-title">Sales</div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-item">
          <a href="{{ route('orders.index') }}"
             class="sidebar-menu-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
             id="menu-orders" title="Order Management">
            <i class="bi bi-receipt"></i>
            <span>Orders</span>
          </a>
        </li>
      </ul>
    </div>

    {{-- Group: Components --}}
    {{-- <div class="sidebar-menu-section">
      <div class="sidebar-menu-title">Components</div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-item">
          <a href="{{ route('tables.basic') }}"
             class="sidebar-menu-link {{ request()->routeIs('tables.basic') ? 'active' : '' }}"
             id="menu-basictables" title="Basic Tables">
            <i class="bi bi-table"></i>
            <span>Basic Tables</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="{{ route('ui.forms') }}"
             class="sidebar-menu-link {{ request()->routeIs('ui.forms') ? 'active' : '' }}"
             id="menu-uiforms" title="Forms and Input">
            <i class="bi bi-input-cursor-text"></i>
            <span>Forms &amp; Input</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="{{ route('ui.buttons') }}"
             class="sidebar-menu-link {{ request()->routeIs('ui.buttons') ? 'active' : '' }}"
             id="menu-uibuttons" title="Buttons">
            <i class="bi bi-menu-button-wide-fill"></i>
            <span>Buttons &amp; Alerts</span>
          </a>
        </li>
      </ul>
    </div> --}}

    {{-- Group: Pages --}}
    {{-- <div class="sidebar-menu-section">
      <div class="sidebar-menu-title">Pages</div>
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-item">
          <a href="{{ route('pages.blank') }}"
             class="sidebar-menu-link {{ request()->routeIs('pages.blank') ? 'active' : '' }}"
             id="menu-blankpage" title="Blank Page">
            <i class="bi bi-file-earmark"></i>
            <span>Blank Page</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="{{ route('login') }}"
             class="sidebar-menu-link {{ request()->routeIs('login') ? 'active' : '' }}"
             id="menu-loginpage" title="Login Page">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Login Screen</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="{{ route('pages.404') }}"
             class="sidebar-menu-link {{ request()->routeIs('pages.404') ? 'active' : '' }}"
             id="menu-404" title="404 Page">
            <i class="bi bi-slash-circle"></i>
            <span>Error 404</span>
          </a>
        </li>
      </ul>
    </div> --}}
  </div>

  {{-- Sidebar Profile Card (Dynamic Footer) --}}
  <div class="sidebar-profile">
    <img src="{{ asset('assets/images/avatar.png') }}" alt="{{ auth()->check() ? auth()->user()->name : 'Administrator' }}" class="sidebar-profile-img"
      onerror="this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop'">
    <div class="sidebar-profile-info">
      <div class="sidebar-profile-name">{{ auth()->check() ? auth()->user()->name : 'Administrator' }}</div>
      <div class="sidebar-profile-email">{{ auth()->check() ? auth()->user()->email : 'admin@email.com' }}</div>
    </div>
  </div>
</div>
