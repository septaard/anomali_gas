<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Manajemen Stok Tabung Gas Portabel">
    <title>@yield('title', 'Anomali Gas')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* DESIGN SYSTEM */
        :root {
            --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --bg-base: #0b0f1a;
            --bg-surface: #111827;
            --bg-card: #1a2236;
            --bg-input: #1e293b;
            --bg-overlay: rgba(0, 0, 0, 0.6);
            --border-default: #1e293b;
            --border-subtle: rgba(148, 163, 184, 0.1);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            
            --accent-indigo: #6366f1;
            --accent-indigo-light: #818cf8;
            --accent-emerald: #34d399;
            --accent-amber: #fbbf24;
            --accent-rose: #fb7185;
            --accent-cyan: #22d3ee;
            --accent-purple: #c084fc;

            --gradient-indigo: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --gradient-emerald: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            --gradient-amber: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            --gradient-purple: linear-gradient(135deg, #a855f7 0%, #d8b4fe 100%);
            --gradient-cyan: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);

            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.4);

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --sidebar-width: 260px;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: var(--font-sans); 
            background: var(--bg-base); 
            color: var(--text-primary); 
            min-height: 100vh; 
            line-height: 1.6; 
            -webkit-font-smoothing: antialiased; 
            display: flex;
        }
        body::before {
            content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(ellipse 80% 50% at 20% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 60%),
                        radial-gradient(ellipse 60% 40% at 80% 80%, rgba(34, 211, 238, 0.05) 0%, transparent 60%);
            pointer-events: none; z-index: 0;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(12px);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 50;
        }
        .sidebar-header {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-subtle);
        }
        .logo-icon { width: 36px; height: 36px; background: var(--gradient-indigo); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 0 15px rgba(99,102,241,0.2); }
        .logo-text { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -0.5px; }

        .sidebar-nav { flex: 1; padding: 24px 16px; display: flex; flex-direction: column; gap: 8px; }
        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px; 
            border-radius: var(--radius-md); color: var(--text-secondary); 
            text-decoration: none; font-weight: 600; font-size: 14px;
            transition: all 0.2s;
        }
        .nav-item:hover { background: rgba(99, 102, 241, 0.1); color: var(--text-primary); }
        .nav-item.active { background: var(--gradient-indigo); color: #fff; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

        .sidebar-footer { padding: 20px; border-top: 1px solid var(--border-subtle); }
        .user-profile { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(99, 102, 241, 0.2); display: flex; align-items: center; justify-content: center; color: var(--accent-indigo-light); font-weight: 700; }
        .user-info { flex: 1; overflow: hidden; }
        .user-name { font-size: 14px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 12px; color: var(--text-muted); }
        
        .btn-logout { width: 100%; padding: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(244, 63, 94, 0.1); color: var(--accent-rose); border: 1px solid rgba(244, 63, 94, 0.2); border-radius: var(--radius-sm); font-weight: 600; font-size: 13px; cursor: pointer; transition: 0.2s; }
        .btn-logout:hover { background: rgba(244, 63, 94, 0.2); }

        /* MAIN CONTENT */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }
        .topbar {
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(11, 15, 26, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-subtle);
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .page-title { font-size: 20px; font-weight: 700; color: var(--text-primary); }
        .header-time { font-size: 13px; color: var(--text-muted); font-weight: 500; }

        .content-area { padding: 32px; flex: 1; max-width: 1200px; margin: 0 auto; width: 100%; }

        /* SHARED COMPONENTS */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; font-family: var(--font-sans); border: none; border-radius: var(--radius-md); cursor: pointer; transition: 0.25s; white-space: nowrap; }
        .btn--primary { background: var(--gradient-indigo); color: #fff; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3); }
        .btn--primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4); }
        .btn--ghost { background: rgba(148, 163, 184, 0.08); color: var(--text-secondary); border: 1px solid var(--border-subtle); }
        .btn--ghost:hover { background: rgba(148, 163, 184, 0.15); color: var(--text-primary); }

        .alert-banner { padding: 16px 24px; border-radius: var(--radius-md); margin-bottom: 24px; display: flex; align-items: center; gap: 14px; font-size: 14px; font-weight: 600; }
        .alert-banner--danger { background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.1) 100%); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; }
        .alert-banner--success { background: linear-gradient(135deg, rgba(52, 211, 153, 0.12) 0%, rgba(16, 185, 129, 0.08) 100%); border: 1px solid rgba(52, 211, 153, 0.3); color: #6ee7b7; }
        .alert-banner--error { background: linear-gradient(135deg, rgba(244, 63, 94, 0.12) 0%, rgba(239, 68, 68, 0.08) 100%); border: 1px solid rgba(244, 63, 94, 0.3); color: #fda4af; }
        .alert-dismiss { margin-left: auto; background: none; border: none; color: inherit; cursor: pointer; font-size: 18px; opacity: 0.6; padding: 4px; }
        .alert-dismiss:hover { opacity: 1; }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 7px; }
        .form-input, .form-select { width: 100%; padding: 11px 14px; font-size: 14px; font-family: var(--font-sans); color: var(--text-primary); background: var(--bg-input); border: 1px solid var(--border-default); border-radius: var(--radius-sm); outline: none; transition: 0.25s; }
        .form-input:focus, .form-select:focus { border-color: var(--accent-indigo); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8.825a.5.5 0 01-.354-.146l-3.5-3.5a.5.5 0 11.708-.708L6 7.618l3.146-3.147a.5.5 0 01.708.708l-3.5 3.5A.5.5 0 016 8.825z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        .form-select option { background: var(--bg-card); color: var(--text-primary); }

        .card { background: rgba(26, 34, 54, 0.5); backdrop-filter: blur(12px); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) both; }
        
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); transition: 0.3s; }
            .main-wrapper { margin-left: 0; }
        }
    </style>
    @yield('styles')
</head>
<body>
    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🔥</div>
            <span class="logo-text">Anomali Gas</span>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span></span> Dashboard
            </a>
            <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <span></span> Pengaturan Harga & Stok
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout"> Log Out</button>
            </form>
        </div>
    </aside>

    {{-- MAIN WRAPPER --}}
    <main class="main-wrapper">
        <div class="topbar">
            <h1 class="page-title">@yield('header_title')</h1>
            <div class="header-time" id="header-time"></div>
        </div>
        <div class="content-area">
            @if(session('success'))
                <div class="alert-banner alert-banner--success animate-in">
                    <span>✅</span> <span>{{ session('success') }}</span>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-banner alert-banner--error animate-in">
                    <span>❌</span> <span>{{ session('error') }}</span>
                    <button class="alert-dismiss" onclick="this.parentElement.remove()">✕</button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            document.getElementById('header-time').textContent = now.toLocaleDateString('id-ID', options);
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
    @yield('scripts')
</body>
</html>
