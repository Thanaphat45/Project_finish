<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกบทบาท · ระบบประเมินผู้สูงอายุ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/adl-ui.css') }}?v={{ filemtime(public_path('css/adl-ui.css')) }}">
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px 16px; background: var(--bg); font-family: 'Sarabun', system-ui, sans-serif; color: var(--ink); }
        .role-card { width: 100%; max-width: 440px; background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 28px; }
        .role-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .role-brand img { height: 40px; }
        .role-brand b { display: block; font-size: 16px; }
        .role-brand small { color: var(--muted); font-size: 13px; }
        h1 { font-size: 24px; margin: 0 0 6px; }
        p { margin: 0 0 20px; color: var(--muted); font-size: 16px; }
        .role-btn {
            display: flex; align-items: center; gap: 14px; width: 100%; margin-bottom: 10px; padding: 14px 16px; min-height: 64px;
            background: var(--surface); border: 1.5px solid var(--line-2); border-radius: 12px; cursor: pointer;
            font: 600 17px 'Sarabun', sans-serif; color: var(--ink); text-align: left;
        }
        .role-btn:hover { border-color: var(--brand); background: var(--brand-soft); }
        .role-btn .material-icons { color: var(--brand); font-size: 26px; }
        .role-btn small { display: block; font-weight: 400; font-size: 14px; color: var(--muted); }
        .role-btn .go { margin-left: auto; color: var(--muted); }
        form { margin: 0; }
        .logout { margin-top: 10px; background: none; border: 0; color: var(--muted); font: 15px 'Sarabun', sans-serif; cursor: pointer; text-decoration: underline; }
    </style>
</head>

<body>
    @php
        $meta = [
            'Admin' => ['admin_panel_settings', 'จัดการผู้ใช้ ข่าวสาร และดูประวัติการใช้งาน'],
            'Staff' => ['badge', 'ประเมินผู้สูงอายุ บันทึกการดูแล และรับคำแนะนำ'],
            'Doctor' => ['medical_services', 'ดูผลประเมินและให้คำแนะนำการดูแล'],
        ];
    @endphp
    <main class="role-card">
        <div class="role-brand">
            <img src="{{ url('images/Logo.png') }}" alt="">
            <span><b>ระบบประเมินผู้สูงอายุ</b><small>ภาวะพึ่งพิง (ADL)</small></span>
        </div>
        <h1>เข้าใช้งานในบทบาทใด</h1>
        <p>บัญชีนี้ใช้ได้หลายบทบาท เปลี่ยนภายหลังได้จากเมนูชื่อผู้ใช้มุมขวาบน</p>

        @foreach ($roles as $value => $label)
            <form method="POST" action="{{ route('select-role.submit') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $value }}">
                <button type="submit" class="role-btn">
                    <span class="material-icons">{{ $meta[$value][0] ?? 'person' }}</span>
                    <span>{{ $label }}<small>{{ $meta[$value][1] ?? '' }}</small></span>
                    <span class="material-icons go">chevron_right</span>
                </button>
            </form>
        @endforeach

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout">ออกจากระบบ</button>
        </form>
    </main>
</body>

</html>
