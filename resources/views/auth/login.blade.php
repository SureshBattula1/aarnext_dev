<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <title>AARNEXT - Login</title>
    <style>
        .auth .brand-logo img {
            width: 80px;
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
                            <h4>Hello! Let's get started</h4> 
                            <h6 class="fw-light">Sign in to continue.</h6>
                            
                            <form class="pt-3" method="POST" action="{{ route('login') }}"> <!-- Updated action to point to the correct route -->
                                @csrf
                                <div class="form-group">
                                    <input id="code" type="text" placeholder="Usercode"
                                        class="form form-control @error('code') is-invalid @enderror" name="code"
                                        value="{{ old('code') }}" required autocomplete="code" autofocus>

                                    @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input id="password" type="password" placeholder="Password"
                                        class="form form-control @error('password') is-invalid @enderror"
                                        name="password" required autocomplete="current-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mt-3 d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg fw-medium auth-form-btn">
                                        SIGN IN
                                    </button>
                                </div>

                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="rememberMe">
                                        <label class="form-check-label" for="rememberMe">
                                            Keep me signed in
                                        </label>
                                    </div>
                                    <a href="{{ route('customer.login') }}" class="auth-link text-black">Customer Login</a>
                                </div>

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
