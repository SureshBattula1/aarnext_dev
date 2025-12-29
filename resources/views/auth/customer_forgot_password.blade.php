<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <title>AARNEXT - Forgot Password</title>
    <style>
        .auth .brand-logo img {
            width: 120px;
            margin-left: 100px;
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth px-0">
                <div class="row w-100 mx-0">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                            <div class="brand-logo">
                                <img src="{{ asset('images/arrnext-logo.png') }}" alt="logo">
                            </div>
                            <h4>Forgot Password</h4>
                            <h6 class="fw-light">Enter your email to reset your password.</h6>

                            <form class="pt-3" method="POST" action="{{ route('customer.forgot-password.post') }}">
                                @csrf
                                <div class="form-group">
                                    <input id="email" type="email" placeholder="Email"
                                        class="form form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mt-3 d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg fw-medium auth-form-btn">
                                        SEND RESET LINK
                                    </button>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="{{ route('customer.login') }}" class="auth-link text-black">Back to Login</a>
                                </div>

                                @if (session('status'))
                                    <div class="alert alert-success mt-3">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger mt-3">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('js/template.js') }}"></script>
</body>
</html>
