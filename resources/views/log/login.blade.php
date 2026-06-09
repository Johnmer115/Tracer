@extends('log.layout')

@section('content')
<section class="login-shell">
    <div class="login-panel">
        <div class="login-brand">
            <img src="{{ asset('image/logo/arellano_logo.png') }}" alt="Arellano University logo" class="login-logo">
            <div>
                <h1>Login </h1>
                <p class="login-kicker">AU SARF Tracer</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="login-alert" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('success'))
            <div class="login-alert login-alert--success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="account">Username</label>
                <input
                    type="text"
                    id="account"
                    name="account"
                    value="{{ old('account') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your username or email"
                >
                @error('account')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                    <button type="button" class="password-toggle" id="toggle-password" aria-label="Show password">
                        <i class="fas fa-eye" id="toggle-password-icon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="login-button">
                Sign In
            </button>
        </form>

        <div>
            <p class="login-footer">@2026 AU SARF Tracer. All rights reserved.</p>
        </div>
    </div>
</section>

<script>
    const passwordInput = document.getElementById('password');
    const togglePasswordButton = document.getElementById('toggle-password');
    const togglePasswordIcon = document.getElementById('toggle-password-icon');

    togglePasswordButton.addEventListener('click', function () {
        const showPassword = passwordInput.type === 'password';

        passwordInput.type = showPassword ? 'text' : 'password';
        togglePasswordButton.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        togglePasswordIcon.classList.toggle('fa-eye', !showPassword);
        togglePasswordIcon.classList.toggle('fa-eye-slash', showPassword);
    });
</script>
@endsection
