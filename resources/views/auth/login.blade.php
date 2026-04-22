<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — POS Jeruk Lokal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #e85d04 0%, #f4a261 50%, #2d6a4f 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 16px;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-brand .icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #e85d04, #f4a261);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            font-size: 2rem; color: #fff;
            box-shadow: 0 8px 20px rgba(232,93,4,.35);
        }
        .login-brand h1 { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
        .login-brand p { font-size: .85rem; color: #888; margin-top: 4px; }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; color: #444; margin-bottom: 6px; }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #aaa; font-size: .9rem;
        }
        .form-control {
            width: 100%; padding: 11px 12px 11px 38px;
            border: 1.5px solid #e0e0e0; border-radius: 10px;
            font-size: .9rem; transition: border .2s;
            background: #fafafa;
        }
        .form-control:focus {
            outline: none; border-color: #e85d04;
            background: #fff; box-shadow: 0 0 0 3px rgba(232,93,4,.1);
        }
        .error-msg { color: #dc3545; font-size: .78rem; margin-top: 4px; }
        .remember-row {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 20px; font-size: .85rem; color: #555;
        }
        .remember-row input { accent-color: #e85d04; }
        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #e85d04, #f4a261);
            color: #fff; border: none; border-radius: 10px;
            font-size: 1rem; font-weight: 700; cursor: pointer;
            transition: all .2s; display: flex; align-items: center;
            justify-content: center; gap: 8px;
            box-shadow: 0 4px 15px rgba(232,93,4,.4);
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(232,93,4,.5); }
        .btn-login:active { transform: translateY(0); }
        .login-hint {
            margin-top: 24px; padding: 14px;
            background: #fff8f5; border-radius: 10px;
            border: 1px solid #fde8d8;
        }
        .login-hint p { font-size: .78rem; color: #888; margin-bottom: 6px; font-weight: 600; }
        .login-hint .account {
            display: flex; justify-content: space-between;
            font-size: .78rem; color: #555; margin-bottom: 3px;
        }
        .login-hint .account span:last-child { color: #e85d04; font-weight: 600; }
        .alert-error {
            background: #fff5f5; border: 1px solid #fcc; border-radius: 8px;
            padding: 10px 14px; margin-bottom: 16px;
            color: #c0392b; font-size: .85rem;
            display: flex; align-items: center; gap: 8px;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-brand">
        <div class="icon"><i class="fa-solid fa-store"></i></div>
        <h1>Jeruk Lokal POS</h1>
        <p>No Sugar No Water — 100% Pure Orange 🍊</p>
    </div>

    @if(session('status'))
        <div class="alert-error"><i class="fa-solid fa-circle-info"></i> {{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <i class="fa-solid fa-circle-xmark"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Email</label>
            <div class="input-wrap">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" class="form-control"
                    value="{{ old('email') }}" placeholder="email@contoh.com"
                    required autofocus autocomplete="username">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-wrap">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" class="form-control"
                    placeholder="••••••••" required autocomplete="current-password">
            </div>
        </div>

        <div class="remember-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat saya</label>
        </div>

        <button type="submit" class="btn-login">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk
        </button>
    </form>

    <div class="login-hint">
        <p>Akun Demo:</p>
        <div class="account"><span>Admin</span><span>admin@jeruklokal.com / password</span></div>
        <div class="account"><span>Kasir</span><span>kasir@jeruklokal.com / password</span></div>
    </div>
</div>
</body>
</html>
