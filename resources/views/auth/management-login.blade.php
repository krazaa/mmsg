<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Management sign in · {{ config('app.name', 'MMS Group') }}</title>
    <link rel="icon" href="{{ asset('logo.svg') }}" type="image/svg+xml">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#020617;color:#fff;font-family:Arial,Helvetica,sans-serif}.page{display:grid;min-height:100vh;place-items:center;padding:24px;background:radial-gradient(circle at 85% 10%,#4c1d9566,transparent 35%),radial-gradient(circle at 10% 90%,#07598555,transparent 35%)}.card{width:100%;max-width:430px;overflow:hidden;border:1px solid #ffffff24;border-radius:26px;background:#0f172aeF;box-shadow:0 30px 80px #0008}.bar{height:5px;background:linear-gradient(90deg,#22d3ee,#6366f1,#a855f7)}.body{padding:32px}.logo{display:block;width:180px;max-width:65%;height:auto;margin:0 auto 28px}.eyebrow{color:#a5b4fc;font-size:10px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase}h1{margin:8px 0 0;font-size:27px}p{margin:9px 0 24px;color:#94a3b8;font-size:13px;line-height:1.6}label{display:block;margin-top:15px;color:#cbd5e1;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.7px}input[type=email],input[type=password]{width:100%;margin-top:7px;border:1px solid #334155;border-radius:11px;background:#020617;padding:13px 14px;color:#fff;font-size:14px;outline:none}input:focus{border-color:#818cf8;box-shadow:0 0 0 3px #6366f133}.remember{display:flex;align-items:center;gap:8px;margin:16px 0;color:#94a3b8;font-size:12px;text-transform:none;letter-spacing:0}.button{width:100%;border:0;border-radius:11px;background:linear-gradient(90deg,#4f46e5,#7c3aed);padding:13px;color:#fff;font-size:14px;font-weight:800;cursor:pointer}.error{margin:0 0 16px;border:1px solid #fb718544;border-radius:10px;background:#88133755;padding:11px;color:#fecdd3;font-size:12px}.note{margin:18px 0 0;text-align:center;color:#64748b;font-size:10px}
    </style>
</head>
<body>
<main class="page">
    <section class="card">
        <div class="bar"></div>
        <div class="body">
            <img src="{{ asset('email-logo.png') }}" alt="{{ config('app.name', 'MMS Group') }}" class="logo">
            <div class="eyebrow">Restricted access</div>
            <h1>Management sign in</h1>
            <p>Authorized administrators only. Customer accounts cannot sign in here.</p>
            @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('management.login.store') }}">
                @csrf
                <label>Email address<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label>
                <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
                <label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <button type="submit" class="button">Sign in to management</button>
            </form>
            <div class="note">Access attempts are rate limited for security.</div>
        </div>
    </section>
</main>
</body>
</html>
