<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CargoPulse') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; color: #2c3e50; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        nav { background: #1a2332; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #e0e0e0; text-decoration: none; margin-left: 1rem; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.2s; }
        nav a:hover { background: #2c3e50; color: #fff; }
        nav .brand { font-size: 1.5rem; font-weight: bold; color: #3498db; }
        .card { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { color: #1a2332; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        input[type="text"], input[type="email"], input[type="password"], select, textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        button { background: #3498db; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #2980b9; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-error { background: #fee; color: #c33; border: 1px solid #fcc; }
        .alert-success { background: #efe; color: #3c3; border: 1px solid #cfc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        .role-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem; }
        .role-customer { background: #dbeafe; color: #1e40af; }
        .role-operations { background: #fef3c7; color: #92400e; }
        .role-compliance { background: #d1fae5; color: #065f46; }
        .role-admin { background: #fce7f3; color: #9d174d; }
    </style>
</head>
<body>
    <nav>
        <div class="brand">CargoPulse</div>
        <div>
            @if(Session::has('user'))
                <span style="color: #a0aec0; margin-right: 1rem;">{{ Session::get('user.name') }} ({{ Session::get('user.role') }})</span>
                <a href="/logout">Logout</a>
            @endif
        </div>
    </nav>
    <main class="container">
        @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        @if(Session::has('error'))
            <div class="alert alert-error">{{ Session::get('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>