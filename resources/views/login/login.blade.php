<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ · ระบบประเมินผู้สูงอายุ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/adl-ui.css') }}?v={{ filemtime(public_path('css/adl-ui.css')) }}">
    <style>
        body { margin: 0; min-height: 100vh; display: grid; grid-template-rows: auto 1fr auto; background: var(--bg); font-family: 'Sarabun', system-ui, sans-serif; color: var(--ink); }
        .top { display: flex; align-items: center; gap: 10px; padding: 14px 20px; background: var(--surface); border-bottom: 1px solid var(--line); }
        .top img { height: 36px; }
        .top b { display: block; font-size: 16px; }
        .top small { color: var(--muted); font-size: 13px; }
        .top nav { margin-left: auto; display: flex; gap: 18px; font-size: 15px; }
        .top nav a { color: var(--ink-2); text-decoration: none; }
        .top nav a:hover { color: var(--brand); }
        main { display: grid; place-items: center; padding: 32px 16px; }
        .card { width: 100%; max-width: 420px; background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 28px; }
        h1 { font-size: 26px; margin: 0 0 4px; }
        .sub { margin: 0 0 22px; color: var(--muted); }
        label { display: grid; gap: 6px; font-weight: 600; font-size: 15px; color: var(--ink-2); margin-bottom: 14px; }
        input { font: 17px 'Sarabun', sans-serif; height: 48px; box-sizing: border-box; padding: 8px 14px; border: 1px solid var(--line-2); border-radius: 10px; color: var(--ink); background: var(--surface); }
        input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(29,107,70,.15); }
        .pw { position: relative; }
        .pw input { width: 100%; box-sizing: border-box; padding-right: 48px; }
        .pw button { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); width: 40px; height: 40px; border: 0; background: none; color: var(--muted); cursor: pointer; border-radius: 8px; }
        .submit { width: 100%; min-height: 50px; margin-top: 6px; border: 0; border-radius: 10px; background: var(--brand); color: #fff; font: 700 17px 'Sarabun', sans-serif; cursor: pointer; }
        .submit:hover { background: var(--brand-ink); }
        .error { background: var(--danger-soft); color: var(--danger); border-radius: 10px; padding: 12px 14px; margin-bottom: 16px; }
        .demo { margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--line); }
        .demo p { margin: 0 0 10px; font-size: 15px; color: var(--muted); }
        .demo-btns { display: flex; gap: 8px; flex-wrap: wrap; }
        .demo-btns button { flex: 1; min-height: 44px; border: 1px solid var(--line-2); background: var(--surface); border-radius: 10px; font: 600 15px 'Sarabun', sans-serif; color: var(--ink); cursor: pointer; }
        .demo-btns button:hover { border-color: var(--brand); background: var(--brand-soft); }
        footer { text-align: center; padding: 18px; color: var(--muted); font-size: 14px; }
        :focus-visible { outline: 3px solid #7fb89a; outline-offset: 2px; }
        @media (max-width: 560px) { .top nav { display: none; } .card { padding: 22px; } }
    </style>
</head>

<body>
    <header class="top">
        <img src="{{ url('images/Logo.png') }}" alt="">
        <span><b>ระบบประเมินผู้สูงอายุ</b><small>ภาวะพึ่งพิง (ADL)</small></span>
        <nav aria-label="เมนู">
            <a href="{{ url('/') }}">หน้าหลัก</a>
            <a href="{{ route('about') }}">เกี่ยวกับ</a>
            <a href="{{ route('contact') }}">ติดต่อเรา</a>
        </nav>
    </header>

    <main>
        <div class="card">
            <h1>เข้าสู่ระบบ</h1>
            <p class="sub">ใช้ชื่อผู้ใช้หรืออีเมล และรหัสผ่านของคุณ</p>

            @if (Session::has('fail'))
                <div class="error" role="alert">{{ Session::get('fail') }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="login-form">
                @csrf
                <label for="login">ชื่อผู้ใช้หรืออีเมล
                    <input type="text" name="login" id="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
                </label>
                <label for="password">รหัสผ่าน
                    <span class="pw">
                        <input type="password" name="password" id="password" autocomplete="current-password" required>
                        <button type="button" id="togglePassword" aria-label="แสดงรหัสผ่าน"><span class="material-icons">visibility</span></button>
                    </span>
                </label>
                <button type="submit" class="submit">เข้าสู่ระบบ</button>
            </form>

            <div class="demo">
                <p>ทดลองใช้งาน (บัญชีตัวอย่าง รหัสผ่าน Demo@2026)</p>
                <div class="demo-btns">
                    <button type="button" data-user="demo_staff">เจ้าหน้าที่</button>
                    <button type="button" data-user="demo_doctor">แพทย์</button>
                </div>
            </div>
        </div>
    </main>

    <footer>&copy; 2569 สำนักงานสาธารณสุขตัวอย่าง (Demo)</footer>

    <script>
        const pw = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        toggle.addEventListener('click', () => {
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            toggle.firstElementChild.textContent = show ? 'visibility_off' : 'visibility';
            toggle.setAttribute('aria-label', show ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
        });
        document.querySelectorAll('[data-user]').forEach(b => b.addEventListener('click', () => {
            document.getElementById('login').value = b.dataset.user;
            pw.value = 'Demo@2026';
            document.getElementById('login-form').requestSubmit();
        }));
    </script>
</body>

</html>
