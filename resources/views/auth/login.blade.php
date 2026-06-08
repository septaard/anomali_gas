<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — GasTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --bg-base: #0b0f1a;
            --bg-card: #1a2236;
            --bg-input: #1e293b;
            --border-default: #1e293b;
            --border-subtle: rgba(148, 163, 184, 0.1);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-indigo: #6366f1;
            --accent-rose: #fb7185;
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-sans);
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99, 102, 241, 0.15), transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .auth-card {
            position: relative;
            z-index: 1;
            background: rgba(26, 34, 54, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .auth-subtitle { color: var(--text-secondary); font-size: 14px; }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
        .form-input {
            width: 100%; padding: 12px 16px; font-size: 14px; color: var(--text-primary);
            background: var(--bg-input); border: 1px solid var(--border-default); border-radius: var(--radius-sm);
            outline: none; transition: 0.2s;
        }
        .form-input:focus { border-color: var(--accent-indigo); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
        
        .btn {
            width: 100%; padding: 12px; font-size: 14px; font-weight: 600; border: none; border-radius: var(--radius-sm);
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #fff; cursor: pointer;
            transition: 0.2s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4); }

        .auth-footer { text-align: center; margin-top: 20px; font-size: 14px; color: var(--text-secondary); }
        .auth-footer a { color: var(--accent-indigo); text-decoration: none; font-weight: 500; }
        .auth-footer a:hover { text-decoration: underline; }

        .error-msg { color: var(--accent-rose); font-size: 13px; margin-top: 5px; }
        .alert-error { background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); padding: 12px; border-radius: var(--radius-sm); color: #fda4af; font-size: 13px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">🔥 GasTrack</h1>
            <p class="auth-subtitle">Masuk ke sistem manajemen stok</p>
        </div>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="btn">Masuk</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar Pengelola</a>
        </div>
    </div>
</body>
</html>
