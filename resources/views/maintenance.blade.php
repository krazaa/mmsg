<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title }} · {{ config('app.name', 'MMS Group') }}</title>
    <link rel="icon" href="{{ asset('logo.svg') }}" type="image/svg+xml">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#020617;color:#fff;font-family:Arial,Helvetica,sans-serif}
        .page{position:relative;display:grid;min-height:100vh;place-items:center;overflow:hidden;padding:24px}
        .glow{position:absolute;width:420px;height:420px;border-radius:999px;filter:blur(100px);opacity:.24}.one{right:-130px;top:-140px;background:#7c3aed}.two{bottom:-180px;left:-120px;background:#0ea5e9}
        .card{position:relative;width:100%;max-width:660px;overflow:hidden;border:1px solid #ffffff24;border-radius:30px;background:linear-gradient(135deg,#0f172aeF,#172554eF);box-shadow:0 30px 80px #0008}
        .bar{height:5px;background:linear-gradient(90deg,#22d3ee,#6366f1,#a855f7)}.content{padding:38px}.logo{display:block;width:220px;max-width:70%;height:auto;margin:0 auto}.badge{display:inline-flex;align-items:center;gap:9px;margin-top:34px;border:1px solid #fbbf2440;border-radius:999px;background:#fbbf2415;padding:9px 13px;color:#fde68a;font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase}.dot{width:8px;height:8px;border-radius:50%;background:#fbbf24;box-shadow:0 0 0 5px #fbbf241f}
        h1{margin:20px 0 0;font-size:42px;line-height:1.1;letter-spacing:-1.4px}p{margin:16px 0 0;color:#cbd5e1;font-size:16px;line-height:1.75}.status{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-top:30px;border:1px solid #ffffff1f;border-radius:16px;background:#ffffff0c;padding:16px}.status b{font-size:13px}.status span{color:#94a3b8;font-size:12px}.preview{position:fixed;left:50%;top:16px;z-index:5;transform:translateX(-50%);border-radius:999px;background:#fff;padding:9px 15px;color:#312e81;font-size:11px;font-weight:800;box-shadow:0 10px 30px #0005}
        @media(max-width:520px){.content{padding:27px}.logo{width:180px}h1{font-size:32px}.status{align-items:flex-start;flex-direction:column}}
    </style>
</head>
<body>
@if($preview ?? false)<div class="preview">Maintenance page preview</div>@endif
<main class="page">
    <div class="glow one"></div><div class="glow two"></div>
    <section class="card">
        <div class="bar"></div>
        <div class="content">
            <img src="{{ asset('email-logo.png') }}" alt="{{ config('app.name', 'MMS Group') }}" class="logo">
            <div class="badge"><i class="dot"></i>Scheduled maintenance</div>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            <div class="status"><div><b>Our team is working on it</b><br><span>Your account and records remain secure.</span></div><span>Please try again later</span></div>
        </div>
    </section>
</main>
</body>
</html>
