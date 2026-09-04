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
            <option value="dispatched" @selected($statusFilter === 'dispatched')>Dispatched</option>
            <option value="completed" @selected($statusFilter === 'completed')>Completed</option>
            <option value="cancelled" @selected($statusFilter === 'cancelled')>Cancelled</option>
            <option value="refunded" @selected($statusFilter === 'refunded')>Refunded</option>
            <option value="partially_refunded" @selected($statusFilter === 'partially_refunded')>Partially Refunded</option>
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
                  'dispatched' => 'accent',
                  'cancelled' => 'failed',
                  'refunded' => 'muted',
                  'partially_refunded' => 'accent',
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

                @php $statusOptions = $order->availableStatusOptions(); @endphp
                @if (! empty($statusOptions))
                  <div class="dropdown d-inline-block">
                    <button
                      type="button"
                      class="table-btn-action"
                      title="Change Status"
                      data-bs-toggle="dropdown"
                      data-bs-strategy="fixed"
                      aria-expanded="false">
                      <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      @foreach ($statusOptions as $value => $label)
                        <li>
                          <button
                            type="button"
                            class="dropdown-item js-status-option"
                            data-action-url="{{ route('orders.update-status', $order) }}"
                            data-status="{{ $value }}"
                            data-label="{{ $label }}"
                            data-destructive="{{ in_array($value, ['cancelled', 'refunded']) ? '1' : '0' }}">
                            {{ $label }}
                          </button>
                        </li>
                      @endforeach
                    </ul>
                  </div>
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

  {{-- Shared hidden form used by the per-row "Change Status" dropdowns --}}
  <form id="statusUpdateForm" method="POST" action="">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" id="statusUpdateValue">
  </form>

  {{-- Shared hidden form used by the per-item "Refund" buttons in the order details modal --}}
  <form id="itemRefundForm" method="POST" action="">
    @csrf
    <input type="hidden" name="quantity" id="itemRefundQuantity">
  </form>

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
        const statusForm = document.getElementById('statusUpdateForm');
        const statusValueInput = document.getElementById('statusUpdateValue');

        document.querySelectorAll('.js-status-option').forEach(function (btn) {
          btn.addEventListener('click', function () {
            if (btn.dataset.destructive === '1') {
              const confirmed = confirm('Mark this order as "' + btn.dataset.label + '"? This will also restock its items.');
              if (!confirmed) {
                return;
              }
            }

            statusForm.action = btn.dataset.actionUrl;
            statusValueInput.value = btn.dataset.status;
            statusForm.submit();
          });
        });

        const modalEl = document.getElementById('orderDetailsModal');
        if (!modalEl) {
          return;
        }

        const numberEl = document.getElementById('orderDetailsNumber');
        const bodyEl = document.getElementById('orderDetailsBody');
        const itemRefundUrlTemplate = "{{ route('orders.items.refund', ['order' => ':order', 'item' => ':item']) }}";
        let currentOrderId = null;

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

              const badgeClass = {
                completed: 'success',
                processing: 'info',
                dispatched: 'accent',
                cancelled: 'failed',
                refunded: 'muted',
                partially_refunded: 'accent',
              }[data.display_status] || 'pending';

              currentOrderId = data.order_id;

              const itemsRows = data.items.map(function (item) {
                let refundCell = '';

                if (data.can_refund_items) {
                  if (item.remaining_quantity > 0) {
                    refundCell = '<div class="d-flex align-items-center gap-1">'
                      +   '<input type="number" min="1" max="' + item.remaining_quantity + '" value="' + item.remaining_quantity + '" '
                      +     'class="form-control form-control-sm js-refund-qty" style="width:64px" data-item-id="' + item.id + '">'
                      +   '<button type="button" class="btn btn-sm btn-outline-danger js-refund-item-btn" '
                      +     'data-item-id="' + item.id + '" data-max="' + item.remaining_quantity + '" data-name="' + item.name + '">Refund</button>'
                      + '</div>';
                  } else {
                    refundCell = '<span class="badge-table muted">Fully refunded</span>';
                  }

                  if (item.refunded_quantity > 0) {
                    refundCell += '<div class="text-muted-green" style="font-size:0.75rem;">' + item.refunded_quantity + ' already refunded</div>';
                  }
                }

                return '<tr>'
                  + '<td>'
                  +   '<div class="table-user-cell">'
                  +     '<span class="table-user-avatar d-inline-flex align-items-center justify-content-center text-white"><i class="bi bi-box-seam"></i></span>'
                  +     '<span>' + item.name + (item.variant ? ' <span class="text-muted-green">(' + item.variant + ')</span>' : '') + '</span>'
                  +   '</div>'
                  + '</td>'
                  + '<td>' + item.quantity + '</td>'
                  + '<td class="text-muted-green">$' + item.price + '</td>'
                  + '<td class="fw-bold">$' + item.line_total + '</td>'
                  + (data.can_refund_items ? '<td>' + refundCell + '</td>' : '')
                  + '</tr>';
              }).join('');

              const itemsHeaderRefundCol = data.can_refund_items ? '<th>Refund</th>' : '';

              bodyEl.innerHTML = ''
                + '<div class="order-detail-top d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">'
                +   '<div>'
                +     '<span class="badge-table ' + badgeClass + '">' + data.status_label + '</span>'
                +     '<div class="text-muted-green mt-2" style="font-size:0.85rem;"><i class="bi bi-calendar3 me-1"></i>' + data.date + '</div>'
                +   '</div>'
                +   '<div class="text-end">'
                +     '<div class="text-muted-green" style="font-size:0.8rem;">Payment Method</div>'
                +     '<div class="fw-bold">' + data.payment_method + '</div>'
                +   '</div>'
                + '</div>'

                + '<div class="row g-3 mb-3">'
                +   '<div class="col-md-6">'
                +     '<div class="order-detail-card">'
                +       '<div class="order-detail-card-title"><i class="bi bi-person-circle"></i> Customer</div>'
                +       '<div class="order-detail-row"><span>Name</span><strong>' + data.customer_name + '</strong></div>'
                +       '<div class="order-detail-row"><span>Phone</span><strong>' + data.customer_phone + '</strong></div>'
                +       (data.customer_email ? '<div class="order-detail-row"><span>Email</span><strong>' + data.customer_email + '</strong></div>' : '')
                +     '</div>'
                +   '</div>'
                +   '<div class="col-md-6">'
                +     '<div class="order-detail-card">'
                +       '<div class="order-detail-card-title"><i class="bi bi-geo-alt-fill"></i> Shipping Address</div>'
                +       '<p class="mb-0" style="font-size:0.9rem;">' + data.shipping_address + ', ' + data.city + '</p>'
                +       (data.notes ? '<hr class="my-2"><div class="text-muted-green" style="font-size:0.8rem;"><i class="bi bi-chat-left-text me-1"></i>' + data.notes + '</div>' : '')
                +     '</div>'
                +   '</div>'
                + '</div>'

                + '<div class="order-detail-card p-0 mb-3" style="overflow:hidden;">'
                +   '<div class="table-responsive">'
                +     '<table class="table-custom mb-0">'
                +       '<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Line Total</th>' + itemsHeaderRefundCol + '</tr></thead>'
                +       '<tbody>' + itemsRows + '</tbody>'
                +     '</table>'
                +   '</div>'
                + '</div>'

                + '<div class="d-flex justify-content-end">'
                +   '<div class="order-summary-box">'
                +     '<div class="d-flex justify-content-between gap-4"><span class="text-muted-green">Subtotal</span><span>$' + data.subtotal + '</span></div>'
                +     '<div class="d-flex justify-content-between gap-4 mt-1"><span class="fw-bold">Total</span><span class="fw-bold" style="color:var(--sys-green)">$' + data.total + '</span></div>'
                +   '</div>'
                + '</div>';
            })
            .catch(function () {
              bodyEl.innerHTML = '<div class="text-center text-danger py-4">Could not load order details.</div>';
            });
        });

        bodyEl.addEventListener('click', function (event) {
          const btn = event.target.closest('.js-refund-item-btn');
          if (!btn || !currentOrderId) {
            return;
          }

          const itemId = btn.dataset.itemId;
          const max = parseInt(btn.dataset.max, 10);
          const qtyInput = bodyEl.querySelector('.js-refund-qty[data-item-id="' + itemId + '"]');
          const quantity = qtyInput ? parseInt(qtyInput.value, 10) : NaN;

          if (!quantity || quantity < 1 || quantity > max) {
            alert('Enter a valid quantity between 1 and ' + max + '.');
            return;
          }

          const confirmed = confirm('Refund ' + quantity + ' x "' + btn.dataset.name + '"? This will restock that quantity.');
          if (!confirmed) {
            return;
          }

          const form = document.getElementById('itemRefundForm');
          form.action = itemRefundUrlTemplate.replace(':order', currentOrderId).replace(':item', itemId);
          document.getElementById('itemRefundQuantity').value = quantity;
          form.submit();
        });
      });
    </script>
  @endpush

@endsection
