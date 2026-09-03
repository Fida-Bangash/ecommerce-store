@extends('layouts.admin')

@section('title', 'Product Management')
@section('meta_description', 'Manage store products')

@section('page_header')
@endsection
@section('page_title', 'Product Management')
@section('page_subtitle', 'Add, edit and organize the products in your store.')

@section('content')

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1">Product Management</h4>
      <p class="text-muted-green mb-0">Add, edit and organize the products in your store.</p>
    </div>
    <a href="{{ route('products.create') }}" class="btn-quick-action" id="btn-add-product">
      <i class="bi bi-plus-lg"></i>
      <span>Add Product</span>
    </a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="table-card-custom">

    {{-- Table Header: Search & Filter Controls --}}
    <div class="table-header-control">
      <form method="GET" action="{{ route('products.index') }}" class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          class="table-search-input"
          placeholder="Search products...">
      </form>

      <div class="table-filter-group">
        <form method="GET" action="{{ route('products.index') }}" class="d-flex align-items-center gap-2">
          @if (request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
          @endif

          <select name="category" class="form-select-custom" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                {{ $category->name }}
              </option>
            @endforeach
          </select>

          <select name="stock" class="form-select-custom" onchange="this.form.submit()">
            <option value="">All Stock</option>
            <option value="in_stock" @selected(request('stock') === 'in_stock')>In Stock</option>
            <option value="low_stock" @selected(request('stock') === 'low_stock')>Low Stock</option>
            <option value="out_of_stock" @selected(request('stock') === 'out_of_stock')>Out of Stock</option>
          </select>
        </form>
      </div>
    </div>

    {{-- Products Table --}}
    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Stock</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($products as $product)
            <tr>
              <td>
                <div class="table-user-cell">
                  @if ($product->primary_image_url)
                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="table-user-avatar">
                  @else
                    <span class="table-user-avatar d-inline-flex align-items-center justify-content-center text-white">
                      <i class="bi bi-box-seam"></i>
                    </span>
                  @endif
                  <span class="table-user-name">{{ $product->name }}</span>
                </div>
              </td>
              <td class="text-muted-green">{{ $product->category?->name ?? '—' }}</td>
              <td>${{ number_format((float) $product->regular_price, 2) }}</td>
              <td class="text-muted-green">
                @if ($product->discount_price)
                  ${{ number_format((float) $product->discount_price, 2) }}
                @else
                  —
                @endif
              </td>
              <td>
                {{ $product->stock_quantity }}
                @if ($product->stock_status === 'out_of_stock')
                  <span class="badge-table failed ms-1">Out of Stock</span>
                @elseif ($product->stock_status === 'low_stock')
                  <span class="badge-table pending ms-1">Low Stock</span>
                @else
                  <span class="badge-table success ms-1">In Stock</span>
                @endif
              </td>
              <td>
                @if ($product->isActive())
                  <span class="badge-table success">Active</span>
                @else
                  <span class="badge-table failed">Inactive</span>
                @endif
              </td>
              <td class="text-end">
                <button
                  type="button"
                  class="table-btn-action js-add-stock-btn"
                  title="Add Stock"
                  data-bs-toggle="modal"
                  data-bs-target="#addStockModal"
                  data-product-name="{{ $product->name }}"
                  data-current-stock="{{ $product->stock_quantity }}"
                  data-action-url="{{ route('products.add-stock', $product) }}">
                  <i class="bi bi-box-arrow-in-down"></i>
                </button>
                <button
                  type="button"
                  class="table-btn-action js-stock-history-btn"
                  title="Stock History"
                  data-bs-toggle="modal"
                  data-bs-target="#stockHistoryModal"
                  data-history-url="{{ route('products.stock-history', $product) }}">
                  <i class="bi bi-clock-history"></i>
                </button>
                <a href="{{ route('products.edit', $product) }}" class="table-btn-action" title="Edit Product">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="table-btn-action delete" title="Delete Product">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted-green py-4">No products found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Table Footer: Pagination --}}
    @if ($products->hasPages())
      <div class="table-footer-control">
        <span class="table-pagination-info">
          Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
        </span>
        {{ $products->links() }}
      </div>
    @endif

  </div>

  {{-- Add Stock Modal (shared across all product rows) --}}
  <div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <form id="addStockForm" method="POST" action="">
          @csrf
          @method('PATCH')
          <div class="modal-header">
            <h5 class="modal-title">Add Stock</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-1">Product: <strong id="addStockProductName">—</strong></p>
            <p class="text-muted-green mb-3">Current stock: <span id="addStockCurrent">0</span></p>

            <label for="addStockQuantity" class="form-label">Quantity to add</label>
            <input
              type="number"
              name="quantity"
              id="addStockQuantity"
              class="form-control"
              min="1"
              step="1"
              placeholder="e.g. 20"
              required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Add Stock</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Stock History Modal (shared across all product rows) --}}
  <div class="modal fade" id="stockHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title">Stock History — <span id="stockHistoryProductName">—</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted-green mb-3">Current stock: <strong id="stockHistoryCurrent">0</strong></p>
          <div class="table-responsive">
            <table class="table-custom">
              <thead>
                <tr>
                  <th>Date Added</th>
                  <th>Quantity Added</th>
                  <th>Stock Before</th>
                  <th>Stock After</th>
                  <th>Added By</th>
                </tr>
              </thead>
              <tbody id="stockHistoryBody">
                <tr>
                  <td colspan="5" class="text-center text-muted-green py-4">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const addStockModalEl = document.getElementById('addStockModal');
        if (addStockModalEl) {
          const form = document.getElementById('addStockForm');
          const nameEl = document.getElementById('addStockProductName');
          const currentEl = document.getElementById('addStockCurrent');
          const quantityInput = document.getElementById('addStockQuantity');

          addStockModalEl.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) {
              return;
            }

            form.action = btn.dataset.actionUrl;
            nameEl.textContent = btn.dataset.productName;
            currentEl.textContent = btn.dataset.currentStock;
            quantityInput.value = '';
          });
        }

        const historyModalEl = document.getElementById('stockHistoryModal');
        if (historyModalEl) {
          const historyProductName = document.getElementById('stockHistoryProductName');
          const historyCurrent = document.getElementById('stockHistoryCurrent');
          const historyBody = document.getElementById('stockHistoryBody');

          historyModalEl.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) {
              return;
            }

            historyProductName.textContent = '—';
            historyCurrent.textContent = '0';
            historyBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted-green py-4">Loading...</td></tr>';

            fetch(btn.dataset.historyUrl, {
              headers: { 'Accept': 'application/json' },
            })
              .then(function (response) { return response.json(); })
              .then(function (data) {
                historyProductName.textContent = data.product;
                historyCurrent.textContent = data.current_stock;

                if (!data.history.length) {
                  historyBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted-green py-4">No stock has been added yet.</td></tr>';
                  return;
                }

                historyBody.innerHTML = data.history.map(function (row) {
                  return '<tr>'
                    + '<td>' + row.date + '</td>'
                    + '<td>+' + row.quantity + '</td>'
                    + '<td>' + row.stock_before + '</td>'
                    + '<td>' + row.stock_after + '</td>'
                    + '<td>' + (row.added_by || '—') + '</td>'
                    + '</tr>';
                }).join('');
              })
              .catch(function () {
                historyBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Could not load stock history.</td></tr>';
              });
          });
        }
      });
    </script>
  @endpush

@endsection
