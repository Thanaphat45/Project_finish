{{--
    Signed-in app shell: slim top bar + one left menu.
    Public pages (not signed in) keep layout.nav.
--}}
@php
    $user = Auth::user();
    $role = $user->Type_Personnel;
    $roleNames = ['Admin' => 'ผู้ดูแลระบบ', 'Staff' => 'เจ้าหน้าที่', 'Doctor' => 'แพทย์'];

    // [label, url, active?, icon]  — null label = section heading
    $menu = [['หน้าหลักของระบบ', url('/'), Request::is('/'), 'home']];
    if ($role === 'Staff') {
        $menu = array_merge($menu, [
            ['ผู้สูงอายุในความดูแล', route('dashboard'), Request::is('dashboard') || Request::is('elderly*'), 'groups'],
            [null, 'แบบประเมิน'],
            ['ADL · กิจวัตรประจำวัน', route('adl.index'), Request::is('adl*'), 'accessibility_new'],
            ['TAI · สุขภาพ', route('tai.index'), Request::is('tai*'), 'monitor_heart'],
            ['CG · ผู้ดูแล', route('cg.index'), Request::is('cg*'), 'volunteer_activism'],
            ['ACG · เยี่ยมบ้าน', route('acg.index'), Request::is('acg*'), 'house'],
            [null, 'รายงานและคำแนะนำ'],
            ['รายงานการปฏิบัติงาน', route('performanceReport.index'), Request::is('performance-report*'), 'description'],
            ['คำแนะนำจากแพทย์', route('care_instructions.index'), Request::is('care-instructions*') && request('unconfirmed') !== 'true', 'medical_information'],
            ['รอยืนยันคำแนะนำ', route('care_instructions.index', ['unconfirmed' => 'true']), request('unconfirmed') === 'true', 'pending_actions'],
        ]);
    } elseif ($role === 'Doctor') {
        $menu = array_merge($menu, [
            ['ผู้สูงอายุและผลประเมิน', route('dashboard'), Request::is('dashboard'), 'groups'],
            ['คำแนะนำการดูแล', route('care_instructions.index'), Request::is('care-instructions*'), 'medical_information'],
        ]);
    } elseif ($role === 'Admin') {
        $menu = array_merge($menu, [
            ['จัดการผู้ใช้', route('dashboard'), Request::is('dashboard'), 'manage_accounts'],
            ['จัดการข่าวสาร', route('admin.layout-admin'), Request::is('layout-admin'), 'campaign'],
            ['ประวัติการใช้งาน', route('audit-logs.index'), Request::is('audit-logs*'), 'history'],
        ]);
    }

    $pending = \App\Models\CareInstruction::where('Name_Staff', $user->Name_User)->whereNull('Confirm')->get();
@endphp

<header class="app-top">
    <button class="app-menu-btn" type="button" aria-label="เปิดเมนู" aria-controls="app-side" aria-expanded="false"
        onclick="document.body.classList.toggle('app-menu-open'); this.setAttribute('aria-expanded', document.body.classList.contains('app-menu-open'))">
        <span class="material-icons">menu</span>
    </button>
    <a href="{{ route('dashboard') }}" class="app-brand">
        <img src="{{ url('images/Logo.png') }}" alt="">
        <span><b>ระบบประเมินผู้สูงอายุ</b><small>ภาวะพึ่งพิง (ADL)</small></span>
    </a>

    <div class="app-top-right">
        @if($user->is_admin_permanent && $role !== 'Admin')
            <span class="app-chip warn">มุมมอง{{ $roleNames[$role] ?? '' }}</span>
        @endif

        <details class="app-pop">
            <summary aria-label="การแจ้งเตือน {{ $pending->count() }} รายการ">
                <span class="material-icons">notifications</span>
                @if($pending->isNotEmpty())<i class="app-dot">{{ $pending->count() }}</i>@endif
            </summary>
            <div class="app-pop-panel">
                <p class="app-pop-title">คำแนะนำที่รอยืนยัน</p>
                @forelse ($pending as $n)
                    <a href="{{ route('care_instructions.index', ['unconfirmed' => 'true']) }}">
                        <b>{{ $n->Name_Elderly }}</b><span>{{ \Illuminate\Support\Str::limit($n->Care_instructions, 70) }}</span>
                    </a>
                @empty
                    <p class="app-muted">ไม่มีรายการรอยืนยัน</p>
                @endforelse
            </div>
        </details>

        <details class="app-pop">
            <summary class="app-user">
                <img src="{{ $user->image_url }}" alt="">
                <span><b>{{ $user->Name_User }}</b><small>{{ $roleNames[$role] ?? '' }}</small></span>
                <span class="material-icons">expand_more</span>
            </summary>
            <div class="app-pop-panel">
                <a href="{{ url('profile-user') }}"><span class="material-icons">person</span>โปรไฟล์ของฉัน</a>
                @if($user->is_admin_permanent)
                    @if($role !== 'Admin')
                        <a href="{{ route('admin.revert-role') }}"><span class="material-icons">undo</span>กลับสู่ผู้ดูแลระบบ</a>
                    @else
                        <a href="{{ route('admin.switch-role', 'Staff') }}"><span class="material-icons">badge</span>ดูในมุมเจ้าหน้าที่</a>
                        <a href="{{ route('admin.switch-role', 'Doctor') }}"><span class="material-icons">stethoscope</span>ดูในมุมแพทย์</a>
                    @endif
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"><span class="material-icons">logout</span>ออกจากระบบ</button>
                </form>
            </div>
        </details>
    </div>
</header>

<nav class="app-side" id="app-side" aria-label="เมนูหลัก">
    <ul>
        @foreach ($menu as $item)
            @if ($item[0] === null)
                <li class="app-side-head">{{ $item[1] }}</li>
            @else
                <li>
                    <a href="{{ $item[1] }}" @class(['on' => $item[2]]) @if($item[2]) aria-current="page" @endif>
                        <span class="material-icons">{{ $item[3] }}</span>{{ $item[0] }}
                        @if ($item[3] === 'pending_actions' && $pending->isNotEmpty())<i class="app-count">{{ $pending->count() }}</i>@endif
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
<div class="app-scrim" onclick="document.body.classList.remove('app-menu-open')"></div>

<script>
    // Close an open popover when clicking elsewhere.
    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.app-pop[open]').forEach(function (d) {
            if (!d.contains(e.target)) d.removeAttribute('open');
        });
    });
</script>
