@extends('layouts.app')

@section('title', 'คำแนะนำการดูแล')

@php
    // Same role logic as before (admins can act as another role).
    $role = session('impersonate_role', Auth::user()->Type_Personnel);
    $onlyPending = request('unconfirmed') === 'true';
    $pendingCount = \App\Models\CareInstruction::whereNull('Confirm')->count();
    $thaiDate = function ($d) {
        if (!$d) return '–';
        $c = \Carbon\Carbon::parse($d);
        return $c->locale('th')->translatedFormat('j M') . ' ' . ($c->year + 543);
    };
@endphp

@section('content')
    @foreach (['success' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls)
        @if (session($key))<div class="alert alert-{{ $cls }}">{!! nl2br(e(session($key))) !!}</div>@endif
    @endforeach

    <div class="page-head">
        <div>
            <h1>คำแนะนำการดูแลจากแพทย์</h1>
            <p>{{ $role === 'Doctor' ? 'คำแนะนำที่คุณให้ไว้ และสถานะการรับทราบของเจ้าหน้าที่' : 'อ่านคำแนะนำ แล้วกด "ยืนยันรับทราบ" เพื่อแจ้งแพทย์ว่าได้นำไปดูแลแล้ว' }}</p>
        </div>
        @if ($role === 'Doctor')
            <div class="actions">
                <a href="{{ route('report.ci.all.pdf', request()->query()) }}" target="_blank" rel="noopener" class="btn btn-light"><span class="material-icons">print</span>พิมพ์รายงานทั้งหมด</a>
            </div>
        @endif
    </div>

    <section class="panel">
        <form method="GET" action="{{ route('care_instructions.index') }}" class="toolbar" data-ci-filter>
            <div class="seg" role="group" aria-label="สถานะ">
                <button type="submit" name="unconfirmed" value="true" @class(['on' => $onlyPending])>รอยืนยัน <span class="seg-count">{{ $pendingCount }}</span></button>
                <button type="submit" name="unconfirmed" value="false" @class(['on' => !$onlyPending])>ทั้งหมด</button>
            </div>
            <label class="grow">ค้นหา
                <span class="search-box">
                    <span class="material-icons">search</span>
                    <input type="search" name="search" class="form-control" placeholder="ชื่อผู้สูงอายุ หรือคำในคำแนะนำ" value="{{ request('search') }}" autocomplete="off">
                </span>
            </label>
            <input type="hidden" name="unconfirmed" value="{{ $onlyPending ? 'true' : 'false' }}" data-keep disabled>
            @if (request('elderly_id'))<input type="hidden" name="elderly_id" value="{{ request('elderly_id') }}">@endif
        </form>
        <div class="result-count">
            พบ {{ $careInstructions->total() }} รายการ
            @if (request()->filled('search') || request('elderly_id'))
                · <a href="{{ route('care_instructions.index', ['unconfirmed' => $onlyPending ? 'true' : 'false']) }}">ล้างการค้นหา</a>
            @endif
        </div>

        <ul class="ci-list">
            @forelse ($careInstructions as $ci)
                <li @class(['ci', 'pending' => !$ci->Confirm])>
                    <div class="ci-head">
                        <div>
                            <a class="ci-name" href="{{ route('elderly.profile', $ci->ID_Elderly) }}">{{ $ci->Name_Elderly }}</a>
                            <p class="ci-meta">
                                {{ $thaiDate($ci->Date_CI) }} · แพทย์ {{ $ci->Name_Doctor }}
                                @if ($role === 'Doctor' && $ci->Name_Staff) · เจ้าหน้าที่ {{ $ci->Name_Staff }} @endif
                                @if (optional($ci->elderly)->Phone_Elderly) · โทร {{ $ci->elderly->Phone_Elderly }} @endif
                            </p>
                        </div>
                        @if ($ci->Confirm)
                            <span class="pill ok">รับทราบแล้ว</span>
                        @else
                            <span class="pill todo">รอเจ้าหน้าที่ยืนยัน</span>
                        @endif
                    </div>

                    <p class="ci-text">{{ $ci->Care_instructions }}</p>

                    <div class="ci-actions">
                        @if (($role === 'Staff' || $role === 'Admin') && empty($ci->Confirm))
                            <form action="{{ route('care_instructions.confirm', ['id' => $ci->ID_CI]) }}" method="POST">
                                @csrf @method('PUT')
                                <button type="submit" class="btn btn-primary"><span class="material-icons">task_alt</span>ยืนยันรับทราบ</button>
                            </form>
                        @endif
                        <a href="{{ route('report.ci.pdf', ['id' => $ci->ID_CI]) }}" target="_blank" rel="noopener" class="btn btn-light btn-sm"><span class="material-icons">picture_as_pdf</span>พิมพ์</a>
                        @if ($ci->elderly)
                            <a href="{{ route('search-location', ['id' => $ci->elderly->ID_Elderly]) }}" target="_blank" rel="noopener" class="btn btn-light btn-sm"><span class="material-icons">place</span>แผนที่บ้าน</a>
                        @endif
                        @if ($role === 'Doctor')
                            <a href="{{ route('care_instructions.edit', ['id' => $ci->ID_CI]) }}" class="btn btn-light btn-sm"><span class="material-icons">edit</span>แก้ไข</a>
                        @endif
                        @if ($role === 'Admin' || ($role === 'Staff' && $ci->Confirm) || $role === 'Doctor')
                            <details class="app-pop">
                                <summary class="btn btn-light btn-sm" aria-label="ตัวเลือกเพิ่มเติม"><span class="material-icons">more_horiz</span></summary>
                                <div class="app-pop-panel">
                                    @if ($role === 'Admin' || ($role === 'Staff' && $ci->Confirm))
                                        <form action="{{ route('care_instructions.unconfirm', ['id' => $ci->ID_CI]) }}" method="POST"
                                            onsubmit="return confirm('ยกเลิกการยืนยันรับทราบของคำแนะนำนี้?')">
                                            @csrf @method('PUT')
                                            <button type="submit"><span class="material-icons">undo</span>ยกเลิกการยืนยัน</button>
                                        </form>
                                    @endif
                                    @if ($role === 'Doctor')
                                        <button type="button" class="text-danger" onclick="confirmDelete('{{ $ci->ID_CI }}')"><span class="material-icons">delete</span>ลบคำแนะนำ</button>
                                    @endif
                                </div>
                            </details>
                            @if ($role === 'Doctor')
                                <form id="delete-ci-form-{{ $ci->ID_CI }}" action="{{ route('care_instructions.destroy', ['id' => $ci->ID_CI]) }}" method="POST" hidden>@csrf @method('DELETE')</form>
                            @endif
                        @endif
                    </div>
                </li>
            @empty
                <li class="empty">
                    <span class="material-icons">{{ $onlyPending ? 'task_alt' : 'medical_information' }}</span>
                    {{ $onlyPending ? 'ไม่มีคำแนะนำที่รอยืนยัน' : 'ยังไม่มีคำแนะนำ' }}
                </li>
            @endforelse
        </ul>
        @if ($careInstructions->hasPages())
            <div class="panel-pad">{{ $careInstructions->appends(request()->query())->links() }}</div>
        @endif
    </section>
@endsection

@push('styles')
    <style>
        .seg-count { display: inline-block; min-width: 22px; padding: 0 6px; margin-left: 4px; border-radius: 999px; background: var(--warn-soft); color: var(--warn); font-size: 13px; }
        .ci-list { list-style: none; margin: 0; padding: 0; }
        .ci { padding: 18px 20px; border-top: 1px solid var(--line); }
        .ci:first-child { border-top: 0; }
        .ci.pending { background: #fffcf4; }
        .ci-head { display: flex; gap: 12px; align-items: flex-start; justify-content: space-between; }
        .ci-name { font-size: 18px; font-weight: 700; color: var(--ink) !important; text-decoration: none !important; }
        .ci-meta { margin: 2px 0 0; color: var(--muted); font-size: 15px; }
        .ci-text { margin: 10px 0 12px; white-space: pre-line; font-size: 17px; line-height: 1.7; max-width: 72ch; }
        .ci-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .ci-actions form { margin: 0; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const form = document.querySelector('[data-ci-filter]');
            const q = form.querySelector('input[name=search]');
            const keep = form.querySelector('[data-keep]');
            let t;
            q.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { keep.disabled = false; form.submit(); }, 600); });
            if (q.value) { q.focus(); q.setSelectionRange(q.value.length, q.value.length); }
        })();
        function confirmDelete(id) {
            Swal.fire({
                title: 'ลบคำแนะนำนี้?', text: 'ลบแล้วกู้คืนไม่ได้', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true
            }).then(r => { if (r.isConfirmed) document.getElementById('delete-ci-form-' + id).submit(); });
        }
    </script>
@endpush
