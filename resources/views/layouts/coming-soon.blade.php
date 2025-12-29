@extends('layouts.app')

@section('content')
<div class="coming-soon-container">
    <h1 class="coming-soon-text">Coming Soon</h1>
</div>

<style>
    /* Centering the content */
    .coming-soon-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color:rgb(241, 241, 241);
        color: white;
        font-family: 'Poppins', sans-serif;
    }

    /* Animated Text */
    .coming-soon-text {
        font-size: 3rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 3px;
        position: relative;
        animation: fadeIn 2s ease-in-out, glow 1.5s infinite alternate;
    }

    /* Fade-in animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Glowing effect */
    @keyframes glow {
        from {
            text-shadow: 0 0 5px #ffcc00, 0 0 10px #ffcc00;
        }
        to {
            text-shadow: 0 0 10px #ff6600, 0 0 20px #ff6600;
        }
    }
</style>
@endsection
