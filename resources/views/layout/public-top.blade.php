{{-- Top bar for visitors who are not signed in (home, about, contact, news). --}}
<header class="site-top">
    <a href="{{ url('/') }}" class="site-brand">
        <img src="{{ url('images/Logo.png') }}" alt="">
        <span><b>ระบบประเมินผู้สูงอายุ</b><small>ภาวะพึ่งพิง (ADL)</small></span>
    </a>
    <nav aria-label="เมนูหลัก" class="site-nav">
        <a href="{{ url('/') }}" @class(['on' => Request::is('/')])>หน้าหลัก</a>
        <details class="site-more">
            <summary @class(['on' => Request::is('about', 'history', 'personnel', 'vision')])>เกี่ยวกับ<span class="material-icons">expand_more</span></summary>
            <div>
                <a href="{{ route('about') }}">เกี่ยวกับสำนักงาน</a>
                <a href="{{ route('history') }}">ประวัติสำนักงาน</a>
                <a href="{{ route('personnel') }}">คณะบุคลากร</a>
                <a href="{{ route('vision') }}">วิสัยทัศน์ / พันธกิจ</a>
            </div>
        </details>
        <a href="{{ route('contact') }}" @class(['on' => Request::is('contact')])>ติดต่อเรา</a>
    </nav>
    <a href="{{ url('login') }}" class="site-login">เข้าสู่ระบบ</a>
</header>
