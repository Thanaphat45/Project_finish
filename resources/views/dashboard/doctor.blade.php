{{-- Doctor home: elders in the doctor's ADL group, and whether today's advice is given. --}}
@php
    $group = auth()->user()->doctorAdlGroup();
    $groupPill = fn ($g) => match ($g) {
        'กลุ่มติดเตียง' => 'bad', 'กลุ่มติดบ้าน' => 'todo', 'กลุ่มติดสังคม' => 'ok', default => 'none',
    };
    $thaiDate = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('th')->translatedFormat('j M') . ' ' . (\Carbon\Carbon::parse($d)->year + 543) : '–';
@endphp

<div class="page-head">
    <div>
        <h1>ผู้สูงอายุที่ดูแล</h1>
        <p>{{ $group ? 'เฉพาะ' . $group : 'ทุกกลุ่ม' }} · ทั้งหมด {{ number_format($stats['total_patients']) }} คน</p>
    </div>
    <div class="actions">
        <a href="{{ route('care_instructions.index', ['unconfirmed' => 'true']) }}" class="btn btn-light">
            <span class="material-icons">pending_actions</span>คำแนะนำที่รอเจ้าหน้าที่ยืนยัน ({{ $stats['pending_ci'] }})
        </a>
    </div>
</div>

<div class="tiles">
    <div class="panel tile"><small>ผู้สูงอายุในกลุ่มที่ดูแล</small><b>{{ $stats['total_patients'] }}</b></div>
    <div class="panel tile"><small>คำแนะนำที่รอเจ้าหน้าที่ยืนยัน</small><b>{{ $stats['pending_ci'] }}</b></div>
    <div class="panel tile"><small>มีบันทึกการดูแล (CG) วันนี้</small><b>{{ $stats['today_assessed'] }}</b></div>
</div>

<section class="panel">
    <form method="GET" action="{{ route('dashboard') }}" class="toolbar" id="doc-filter">
        <label>ช่วงอายุ
            <select name="age_range" class="form-control">
                <option value="">ทุกช่วง</option>
                @foreach (['60-69' => '60–69 ปี', '70-79' => '70–79 ปี', '80-89' => '80–89 ปี', '90+' => '90 ปีขึ้นไป'] as $v => $t)
                    <option value="{{ $v }}" @selected(request('age_range') === $v)>{{ $t }}</option>
                @endforeach
            </select>
        </label>
        <noscript><button class="btn btn-primary">กรอง</button></noscript>
    </form>
    <div class="result-count">
        แสดง {{ $elderlys->count() }} จาก {{ $elderlys->total() }} คน
        @if (request('age_range')) · <a href="{{ route('dashboard') }}">ล้างตัวกรอง</a>@endif
    </div>

    <div class="table-responsive">
        <table class="table list-table">
            <thead>
                <tr>
                    <th>ผู้สูงอายุ</th>
                    <th>ADL ล่าสุด</th>
                    <th>บันทึกการดูแลล่าสุด</th>
                    <th>คำแนะนำ</th>
                    <th><span class="sr-only">จัดการ</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($elderlys as $e)
                    @php
                        $adl = $e->barthel_adl;
                        $hasToday = $e->care_instructions->where('Date_CI', now()->toDateString())->isNotEmpty();
                        $hasPending = $e->care_instructions->whereNull('Confirm')->isNotEmpty();
                    @endphp
                    <tr>
                        <td>
                            <a class="who" href="{{ route('elderly.profile', $e->ID_Elderly) }}">
                                <img src="{{ $e->image_url }}" alt="">
                                <span><b>{{ $e->Name_Elderly }}</b><small>{{ \Carbon\Carbon::parse($e->Birthday)->age }} ปี</small></span>
                            </a>
                        </td>
                        <td>
                            @if ($adl)
                                <span class="score"><b>{{ $adl->Score_ADL }}</b><small>/20</small>
                                    <span class="pill {{ $groupPill($adl->Group_ADL) }}">{{ str_replace('กลุ่ม', '', $adl->Group_ADL) }}</span></span>
                            @else
                                <span class="pill none">ยังไม่ประเมิน</span>
                            @endif
                        </td>
                        <td>
                            @if ($e->care_giver)
                                {{ $e->care_giver->Name_CG ?? '–' }}<small class="d-block text-muted">{{ $thaiDate($e->care_giver->Date_CG) }}</small>
                            @else
                                <span class="text-muted">ยังไม่มี</span>
                            @endif
                        </td>
                        <td>
                            @if ($hasToday)
                                <span class="pill ok">ให้แล้ววันนี้</span>
                            @elseif ($hasPending)
                                <a href="{{ route('care_instructions.index', ['elderly_id' => $e->ID_Elderly, 'unconfirmed' => 'true']) }}" class="pill todo">รอเจ้าหน้าที่ยืนยัน</a>
                            @else
                                <span class="pill none">ยังไม่มี</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                @if ($hasToday)
                                    <a href="{{ route('care_instructions.index', ['elderly_id' => $e->ID_Elderly]) }}" class="btn btn-light btn-sm">ดูคำแนะนำ</a>
                                @else
                                    <a href="{{ route('care_instructions.create', ['elderly_id' => $e->ID_Elderly]) }}" class="btn btn-primary btn-sm">ให้คำแนะนำ</a>
                                @endif
                                <details class="app-pop">
                                    <summary class="btn btn-light btn-sm" aria-label="ดูผลประเมินของ {{ $e->Name_Elderly }}"><span class="material-icons">more_horiz</span></summary>
                                    <div class="app-pop-panel">
                                        <a href="#" data-toggle="modal" data-target="#adlModal-{{ $e->ID_Elderly }}"><span class="material-icons">accessibility_new</span>ดูผล ADL</a>
                                        <a href="#" data-toggle="modal" data-target="#cgDatesModal-{{ $e->ID_Elderly }}"><span class="material-icons">volunteer_activism</span>ดูบันทึกการดูแล (CG)</a>
                                        <a href="{{ route('elderly.profile', $e->ID_Elderly) }}"><span class="material-icons">history</span>ประวัติทั้งหมด</a>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty"><span class="material-icons">groups</span>
                        {{ request('age_range') ? 'ไม่พบผู้สูงอายุในช่วงอายุนี้' : 'ยังไม่มีผู้สูงอายุในกลุ่มที่คุณดูแล' }}
                    </div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($elderlys->hasPages())<div class="panel-pad">{{ $elderlys->links() }}</div>@endif
</section>

@push('styles')
<style>
    .tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 20px; }
    .tile { padding: 16px 20px; display: grid; gap: 2px; }
    .tile small { color: var(--ink-2); font-size: 15px; }
    .tile b { font-size: 30px; }
    .list-table .who small { display: block; }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('#doc-filter select').forEach(s => s.addEventListener('change', () => s.form.submit()));
    // Close the "more" popover when a modal opens from it.
    document.querySelectorAll('.app-pop-panel [data-toggle="modal"]').forEach(a => a.addEventListener('click', e => {
        e.preventDefault(); a.closest('details').removeAttribute('open');
    }));
</script>
@endpush
