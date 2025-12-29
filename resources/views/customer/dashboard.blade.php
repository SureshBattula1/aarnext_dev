@extends('layouts.customer-header')

@push('styles')
<style>
  /* Put modal/backdrop above sticky headers/navbars/sidebars */
  .modal-backdrop { z-index: 1999 !important; }
  .modal { z-index: 2000 !important; }

  /* Lower any header/nav while modal is open (adjust selectors to your layout) */
  .modal-open .navbar,
  .modal-open .app-header,
  .modal-open header,
  .modal-open .customer-header {
    z-index: 1000 !important;
  }

  /* Some templates dim/blur main content—neutralize while modal is open */
  .modal-open .app-content,
  .modal-open .content-wrapper,
  .modal-open .page-wrapper,
  .modal-open .container-wrapper {
    opacity: 1 !important;
    filter: none !important;
  }
</style>
@endpush

@section('content')
@php
    $checkSetup = Auth::guard('customer')->user()->customer_login_setup ?? 0;
@endphp

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-warning text-white">
        <h4 class="card-title mb-0">
          <i class="ti-dashboard"></i> Customer Dashboard
        </h4>
      </div>

      <div class="card-body">
        {{-- ==== Dashboard Cards ==== --}}
        <div class="row mt-4">
          <div class="col-md-1"></div>

          <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
              <div class="card-body text-center">
                <i class="ti-shopping-cart display-4 mb-3"></i>
                <h5 class="card-title">My Orders</h5>
                <p class="card-text">View your order history and track current orders</p>
                <a href="{{ route('customer.order.reports') }}" class="btn btn-light btn-sm">View Orders</a>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card bg-success text-white h-100">
              <div class="card-body text-center">
                <i class="ti-receipt display-4 mb-3"></i>
                <h5 class="card-title">Invoices</h5>
                <p class="card-text">View and download your invoices</p>
                <a href="{{ route('customer.invoice.reports') }}" class="btn btn-light btn-sm">View Invoices</a>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card bg-info text-white h-100">
              <div class="card-body text-center">
                <i class="ti-user display-4 mb-3"></i>
                <h5 class="card-title">Profile</h5>
                <p class="card-text">Update your profile information</p>
                <a href="#" class="btn btn-light btn-sm">Edit Profile</a>
              </div>
            </div>
          </div>
        </div>

        {{-- ==== Recent Invoices & Notifications ==== --}}
        <div class="row mt-4">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="ti-time"></i> Recent Invoices</h5>
              </div>
              <div class="card-body">
                @if($recentOrders->count() > 0)
                  <ul class="list-group list-group-flush">
                    @foreach($recentOrders as $invoices)
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <strong>{{ $invoices->sales_invoice_no }}</strong><br>
                          <small class="text-muted">{{ \Carbon\Carbon::parse($invoices->order_date)->format('M d, Y') }}</small>
                        </div>
                        <div class="text-right">
                          <small class="text-muted">{{ number_format($invoices->total, 2) }}</small>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="text-muted">No recent Invoices to display.</p>
                @endif
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="ti-bell"></i> Sales Approvals Notifications</h5>
              </div>
              <div class="card-body">
                @if($ordersApprovals->count() > 0)
                  <ul class="list-group list-group-flush">
                    @foreach($ordersApprovals as $order)
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <strong>{{ $order->sales_invoice_no }}</strong><br>
                          <small class="text-muted">{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</small>
                        </div>
                        <div class="text-right">
                          <span class="badge
                            @if($order->status == 0) badge-warning
                            @elseif($order->status == 1) badge-success
                            @else badge-danger @endif">
                            @if($order->status == 0) Pending
                            @elseif($order->status == 1) Approved
                            @else Rejected @endif
                          </span><br>
                          <small class="text-muted">{{ number_format($order->total, 2) }}</small>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <p class="text-muted">No recent Sales Approvals to display.</p>
                @endif
              </div>
            </div>
          </div>
        </div>

        {{-- ==== Account Summary ==== --}}
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="ti-stats-up"></i> Account Summary</h5>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-md-3">
                    <h3 class="text-primary">{{ $totalOrders }}</h3>
                    <p class="text-muted">Pending Sales Orders</p>
                  </div>
                  <div class="col-md-3">
                    <h3 class="text-warning">{{ $rejectedOrders }}</h3>
                    <p class="text-muted">Rejected Sales Orders</p>
                  </div>
                  <div class="col-md-3">
                    <h3 class="text-info">{{ $ApprovedOrders }}</h3>
                    <p class="text-muted">Approved Sales Order</p>
                  </div>
                  <div class="col-md-3">
                    <h3 class="text-success">{{ $completedOrders }}</h3>
                    <p class="text-muted">Completed Invoices</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div> {{-- /.card-body --}}
    </div>
  </div>
</div>

{{-- ==== Password Setup Modal ==== --}}
@if ($checkSetup == 0)
  <!-- NOTE: no "fade" class -->
  <div class="modal" id="passwordSetupModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form id="passwordSetupForm">
          @csrf
          <input type="email" name="email" value="{{ Auth::guard('customer')->user()->email }}" autocomplete="username" hidden>

          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Set Your Password</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="password" class="form-label">New Password</label>
              <div class="input-group">
                <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" minlength="8" required>
                <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#password">
                  <i class="ti-eye"></i>
                </button>
              </div>
              <small class="text-muted">Minimum 8 characters.</small>
            </div>

            <div class="mb-1">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password" minlength="8" required>
                <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="#confirm_password">
                  <i class="ti-eye"></i>
                </button>
              </div>
            </div>

            <small id="passwordMatch" class="text-danger d-none">Passwords do not match.</small>
            <small id="passwordOk" class="text-success d-none">Passwords match.</small>
          </div>

          <div class="modal-footer">
            <button type="submit" id="savePasswordBtn" class="btn btn-success" disabled>Save Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  console.log("Customer dashboard script running!");

  const checkSetup = @json($checkSetup);

  if (Number(checkSetup) === 0) {
    const $m = $('#passwordSetupModal');
    const password        = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const matchText       = document.getElementById('passwordMatch');
    const okText          = document.getElementById('passwordOk');
    const saveBtn         = document.getElementById('savePasswordBtn');
    const form            = document.getElementById('passwordSetupForm');

    // 1) escape stacking contexts & 2) kill fade feel
    $m.appendTo('body');      // ensure modal is not trapped by header wrappers
    $m.removeClass('fade');   // avoid dim/animation flicker

    // 3) show modal (Bootstrap 4)
    $m.modal({ backdrop: 'static', keyboard: false }).modal('show');

    // Toggle password visibility
    document.querySelectorAll('.toggle-pass').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.querySelector(btn.dataset.target);
        const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
        target.setAttribute('type', type);
        btn.querySelector('i')?.classList.toggle('ti-eye-off', type === 'text');
      });
    });

    // Validate passwords in real-time
    const validate = () => {
      const p = password.value.trim();
      const c = confirmPassword.value.trim();
      const matches = p === c && p.length >= 8;
      if (matches) {
        matchText.classList.add('d-none');
        okText.classList.remove('d-none');
        saveBtn.disabled = false;
      } else {
        okText.classList.add('d-none');
        matchText.classList.toggle('d-none', !(p.length && c.length));
        saveBtn.disabled = true;
      }
    };
    password.addEventListener('input', validate);
    confirmPassword.addEventListener('input', validate);

    // Submit form
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      saveBtn.disabled = true;

      fetch("{{ route('customer.updatePassword') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}",
          "Accept": "application/json"
        },
        body: JSON.stringify({
          password: password.value,
          confirm_password: confirmPassword.value
        })
      })
      .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Request failed');
        return data;
      })
      .then(data => {
        if (data.success) {
          alert('Password updated successfully!');
          $m.modal('hide');
          window.location.reload();
        } else {
          alert(data.message || 'Something went wrong!');
          saveBtn.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        alert(err.message || 'Network error');
        saveBtn.disabled = false;
      });
    });
  }
});
</script>
@endpush
