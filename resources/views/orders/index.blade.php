@extends('layouts.admin')

@section('title', 'Order Management')
@section('meta_description', 'Manage customer orders')

@section('page_header')
@endsection
@section('page_title', 'Order Management')
@section('page_subtitle', 'View, cancel and refund customer orders.')

@section('content')

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1">Order Management</h4>
      <p class="text-muted-green mb-0">All orders placed by customers, newest first.</p>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="table-card-custom">

    {{-- Table Header: Search & Filter Controls --}}
    <div class="table-header-control">
      <form method="GET" action="{{ route('orders.index') }}" class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          class="table-search-input"
          placeholder="Search by order #, name or phone...">
        <input type="hidden" name="status" value="{{ $statusFilter }}">
      </form>

      <div class="table-filter-group">
        <form method="GET" action="{{ route('orders.index') }}" class="d-flex align-items-center gap-2">
          @if (request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
          @endif

          <select name="status" class="form-select-custom" onchange="this.form.submit()">
            <option value="" @selected($statusFilter === '')>All Orders</option>
            <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
            <option value="processing" @selected($statusFilter === 'processing')>Processing</option>
            <option value="completed" @selected($statusFilter === 'completed')>Completed</option>
            <option value="cancelled" @selected($statusFilter === 'cancelled')>Cancelled</option>
            <option value="refunded" @selected($statusFilter === 'refunded')>Refunded</option>
          </select>
        </form>
      </div>
    </div>

    {{-- Orders Table --}}
    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($orders as $order)
            @php
              $badgeClass = match ($order->display_status) {
                  'completed' => 'success',
                  'processing' => 'info',
                  'cancelled' => 'failed',
                  'refunded' => 'muted',
                  default => 'pending',
              };
            @endphp
            <tr>
              <td class="table-user-name">{{ $order->order_number }}</td>
              <td>
                <div class="table-user-cell">
                  <span class="table-user-avatar d-inline-flex align-items-center justify-content-center text-white">
                    <i class="bi bi-person"></i>
                  </span>
                  <div>
                    <span class="table-user-name d-block">{{ $order->customer_name }}</span>
                    <span class="table-user-sub">{{ $order->customer_phone }}</span>
                  </div>
                </div>
              </td>
              <td class="text-muted-green">{{ $order->items_count }}</td>
              <td>${{ number_format((float) $order->total, 2) }}</td>
              <td class="text-muted-green text-uppercase">{{ $order->payment_method }}</td>
              <td><span class="badge-table {{ $badgeClass }}">{{ $order->status_label }}</span></td>
              <td class="text-muted-green">{{ $order->created_at->format('d M Y, h:i A') }}</td>
              <td class="text-end">
                <button
                  type="button"
                  class="table-btn-action js-view-order-btn"
                  title="View Order"
                  data-bs-toggle="modal"
                  data-bs-target="#orderDetailsModal"
                  data-details-url="{{ route('orders.show', $order) }}">
                  <i class="bi bi-eye"></i>
                </button>

                @if ($order->canBeCancelled())
                  <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Cancel order {{ $order->order_number }}? Its stock will be restored.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="table-btn-action delete" title="Cancel Order">
                      <i class="bi bi-x-circle"></i>
                    </button>
                  </form>
                @endif

                @if ($order->canBeRefunded())
                  <form action="{{ route('orders.refund', $order) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Mark order {{ $order->order_number }} as refunded? Its stock will be restored.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="table-btn-action" title="Refund Order">
                      <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted-green py-4">No orders found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Table Footer: Pagination --}}
    @if ($orders->hasPages())
      <div class="table-footer-control">
        <span class="table-pagination-info">
          Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </span>
        {{ $orders->links() }}
      </div>
    @endif

  </div>

  {{-- Order Details Modal (shared across all order rows) --}}
  <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4">
        <div class="modal-header">
          <h5 class="modal-title">Order <span id="orderDetailsNumber">—</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="orderDetailsBody" class="text-center text-muted-green py-4">Loading...</div>
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
        const modalEl = document.getElementById('orderDetailsModal');
        if (!modalEl) {
          return;
        }

        const numberEl = document.getElementById('orderDetailsNumber');
        const bodyEl = document.getElementById('orderDetailsBody');

        modalEl.addEventListener('show.bs.modal', function (event) {
          const btn = event.relatedTarget;
          if (!btn) {
            return;
          }

          numberEl.textContent = '—';
          bodyEl.innerHTML = '<div class="text-center text-muted-green py-4">Loading...</div>';

          fetch(btn.dataset.detailsUrl, {
            headers: { 'Accept': 'application/json' },
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              numberEl.textContent = data.order_number;

              const itemsRows = data.items.map(function (item) {
                return '<tr>'
                  + '<td>' + item.name + (item.variant ? ' <span class="text-muted-green">(' + item.variant + ')</span>' : '') + '</td>'
                  + '<td>' + item.quantity + '</td>'
                  + '<td>$' + item.price + '</td>'
                  + '<td>$' + item.line_total + '</td>'
                  + '</tr>';
              }).join('');

              bodyEl.innerHTML = ''
                + '<div class="row mb-3">'
                + '<div class="col-md-6">'
                + '<p class="mb-1"><strong>Customer:</strong> ' + data.customer_name + '</p>'
                + '<p class="mb-1"><strong>Phone:</strong> ' + data.customer_phone + '</p>'
                + (data.customer_email ? '<p class="mb-1"><strong>Email:</strong> ' + data.customer_email + '</p>' : '')
                + '<p class="mb-1"><strong>Address:</strong> ' + data.shipping_address + ', ' + data.city + '</p>'
                + '</div>'
                + '<div class="col-md-6">'
                + '<p class="mb-1"><strong>Status:</strong> ' + data.status_label + '</p>'
                + '<p class="mb-1"><strong>Date:</strong> ' + data.date + '</p>'
                + '<p class="mb-1"><strong>Payment:</strong> ' + data.payment_method + '</p>'
                + (data.notes ? '<p class="mb-1"><strong>Notes:</strong> ' + data.notes + '</p>' : '')
                + '</div>'
                + '</div>'
                + '<div class="table-responsive">'
                + '<table class="table-custom">'
                + '<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Line Total</th></tr></thead>'
                + '<tbody>' + itemsRows + '</tbody>'
                + '</table>'
                + '</div>'
                + '<div class="text-end mt-2">'
                + '<p class="mb-1 text-muted-green">Subtotal: $' + data.subtotal + '</p>'
                + '<p class="mb-0 fw-bold">Total: $' + data.total + '</p>'
                + '</div>';
            })
            .catch(function () {
              bodyEl.innerHTML = '<div class="text-center text-danger py-4">Could not load order details.</div>';
            });
        });
      });
    </script>
  @endpush

@endsection
