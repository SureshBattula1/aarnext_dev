@extends('layouts.app')

@section('content')
<style>
    .container {
       padding: 3rem;
    }
    .table {
        border: 1px solid black;
        margin-top: 20px;
    }
    .table th {
        background-color: #126680;
        color: white;
        padding: 8px;
    }
    .table td {
        padding: 6px;
    }
    .modal-content {
        border-radius: 10px;
        padding: 5px;
    }
    .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }
    .modal-title {
        font-size: 1.1rem;
    }
    .form-label {
        font-weight: 500;
    }
    .form-control {
        border-radius: 6px;
        padding: 10px;
        font-size: 0.95rem;
    }
    .btn {
        border-radius: 6px;
        padding: 8px 15px;
        font-size: 0.9rem;
    }
    .btn-primary {
        background-color: #4a76a8;
        border-color: #4a76a8;
    }
    .btn-primary:hover {
        background-color: #3b5f86;
        border-color: #3b5f86;
    }
    .btn-outline-primary {
        border-color: #4a76a8;
        color: #4a76a8;
    }
    .btn-outline-primary:hover {
        background-color: #4a76a8;
        color: white;
    }
    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    .alert {
        border-radius: 8px;
    }
    .toggle-password {
        position: absolute;
        right: 10px;
        top: 40px;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
    }
</style>

<div class="container mt-4">
    <h4 class="mb-4">Users</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Warehouse</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($users as $user)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $user->code }}</td>
                <td>{{ $user->ware_house }}</td>
                <td>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal{{ $user->id }}">
                        Edit Password
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="changePasswordModal{{ $user->id }}" tabindex="-1" aria-labelledby="changePasswordLabel{{ $user->id }}" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <form action="{{ route('changePasswordPost') }}" method="POST">
                              @csrf
                              <div class="modal-header">
                                <h5 class="modal-title" id="changePasswordLabel{{ $user->id }}">Change Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                  <input type="hidden" name="user_id" value="{{ $user->id }}">
                                  <div class="mb-3 position-relative">
                                    <label for="new-password-{{ $user->id }}" class="form-label">New Password</label>
                                    <input type="password" name="new_password" id="new-password-{{ $user->id }}" class="form-control" required minlength="8" placeholder="Enter new password">
                                    <button type="button" class="toggle-password" onclick="togglePassword('{{ $user->id }}')">
                                        <i id="toggle-icon-{{ $user->id }}" class="ti ti-eye"></i>
                                    </button>
                                  </div>

                                  <div class="mb-3">
                                      <label for="new-password-confirm-{{ $user->id }}" class="form-label">Confirm Password</label>
                                      <input type="password" name="new_password_confirmation" id="new-password-confirm-{{ $user->id }}" class="form-control" required minlength="8" placeholder="Confirm new password">
                                  </div>
                              </div>
                              <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(userId) {
        const input = document.getElementById('new-password-' + userId);
        const icon = document.getElementById('toggle-icon-' + userId);
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('ti-eye');
            icon.classList.add('ti-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('ti-eye-slash');
            icon.classList.add('ti-eye');
        }
    }
</script>
@endpush
