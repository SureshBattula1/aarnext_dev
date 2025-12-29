<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>AARNEXT</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css')}}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/select2.min.css')}}"> --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Include Select2 JS -->

    <link rel="stylesheet" href="{{ asset('css/style.css')}}">

    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css')}}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png')}}" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8" charset="UTF-8"></script>
    <link rel="stylesheet" href="{{ asset('css/custom.css')}}">
    <script>
        var userRole = @json(auth()->user()->role);
    </script>

<style>
    .loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
    }

    .spinner {
        color: #ce0d0d;
    }

    .medical-loader {
        width: 50px;
        height: 50px;
        animation: spin 5s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }


</style>
  </head>
<body>
    <div class="container-scroller">
        @include('layouts.header')
        <div class="container-fluid page-body-wrapper">
            @include('layouts.sidemenu')
            <div class="main-panel">
                @yield('content')
                @include('layouts.footer')
            </div>
        </div>
    </div>
    <!--<div id="loader" class="loader" style="display: none;">
        <div class="spinner">
            <i class="fa fa-spinner fa-spin" style="font-size: 48px;"></i>
        </div>
    </div>-->
    <div id="loader" class="loader" style="display: none;">
        <img src="{{ asset('images/loader-pill.png') }}" alt="Loading..." class="medical-loader">
    </div>
    @stack('scripts')
    <script>
        $(document).ready(function () {
       // Show loader on ajax start
       $(document).ajaxStart(function () {
           $("#loader").show();
       });

       // Hide loader on ajax stop
       $(document).ajaxStop(function () {
           $("#loader").hide();
       });
   });
   </script>
</body>
</html>
