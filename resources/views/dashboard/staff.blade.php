{{-- Staff home: what needs doing today, then the elder list. --}}
@php
    use App\Models\Elderly;
    use App\Models\CareInstruction;

    $elderlies->loadMissing(['barthel_adl', 'care_giver', 'score_tai']);

    $todo = [
        ['คำแนะนำจากแพทย์ที่รอยืนยัน', CareInstruction::whereNull('Confirm')->count(),
            route('care_instructions.index', ['unconfirmed' => 'true']), 'medical_information'],
        ['ยังไม่ได้ประเมิน ADL', Elderly::doesntHave('barthel_adl')->count(), route('adl.index'), 'accessibility_new'],
        ['ยังไม่ได้ประเมิน TAI', Elderly::doesntHave('score_tai')->count(), route('tai.index'), 'favorite'],
        ['ยังไม่ได้บันทึก CG', Elderly::doesntHave('care_giver')->count(), route('cg.index'), 'volunteer_activism'],
    ];
    $todoLeft = collect($todo)->sum(fn ($t) => $t[1]);

    $groupPill = fn ($g) => match ($g) {
        'กลุ่มติดเตียง' => 'bad',
        'กลุ่มติดบ้าน' => 'todo',
        'กลุ่มติดสังคม' => 'ok',
        default => 'none',
    };
    $filtered = request()->hasAny(['search', 'adl_group', 'gender', 'age_range', 'created_date_from', 'created_date_to']);
@endphp

<div class="page-head">
    <div>
        <h1>ผู้สูงอายุในความดูแล</h1>
        <p>ทั้งหมด {{ number_format($elderlies->total()) }} คน</p>
    </div>
    <div class="actions">
        <a href="{{ route('elderly-report') }}" class="btn btn-light"><span class="material-icons">print</span>พิมพ์รายงานสรุป</a>
        <a href="{{ route('add-elderly') }}" class="btn btn-primary"><span class="material-icons">person_add</span>เพิ่มผู้สูงอายุ</a>
    </div>
</div>

<section class="todo panel" aria-labelledby="todo-h">
    <div class="todo-head">
        <h2 id="todo-h">งานที่ต้องทำ</h2>
        @if ($todoLeft === 0)
            <span class="pill ok">ครบทุกรายการแล้ว</span>
        @endif
    </div>
    <div class="todo-grid">
        @foreach ($todo as [$label, $count, $href, $icon])
            <a href="{{ $href }}" @class(['todo-item', 'done' => $count === 0])>
                <span class="material-icons">{{ $count === 0 ? 'check_circle' : $icon }}</span>
                <span class="todo-text">
                    <small>{{ $label }}</small>
                    @if ($count === 0)
                        <b class="none">ไม่มีค้าง</b>
                    @else
                        <b>{{ $count }} <em>รายการ</em></b>
                    @endif
                </span>
                @if ($count > 0)<span class="material-icons todo-go">chevron_right</span>@endif
            </a>
        @endforeach
    </div>
</section>

<section class="panel" aria-label="รายชื่อผู้สูงอายุ">
    <form method="GET" action="{{ route('dashboard') }}" class="toolbar" id="elder-filter">
        <label class="grow">ค้นหาชื่อ
            <span class="search-box">
                <span class="material-icons">search</span>
                <input type="search" name="search" class="form-control" placeholder="พิมพ์ชื่อหรือนามสกุล"
                    value="{{ request('search') }}" autocomplete="off">
            </span>
        </label>
        <label>กลุ่ม ADL
            <select name="adl_group" class="form-control">
                <option value="">ทุกกลุ่ม</option>
                @foreach (['กลุ่มติดสังคม' => 'ติดสังคม', 'กลุ่มติดบ้าน' => 'ติดบ้าน', 'กลุ่มติดเตียง' => 'ติดเตียง'] as $v => $t)
                    <option value="{{ $v }}" @selected(request('adl_group') === $v)>{{ $t }}</option>
                @endforeach
            </select>
        </label>
        <label>เพศ
            <select name="gender" class="form-control">
                <option value="">ทั้งหมด</option>
                <option value="ชาย" @selected(request('gender') === 'ชาย')>ชาย</option>
                <option value="หญิง" @selected(request('gender') === 'หญิง')>หญิง</option>
            </select>
        </label>
        <label>ช่วงอายุ
            <select name="age_range" class="form-control">
                <option value="">ทุกช่วง</option>
                @foreach (['60-69' => '60–69 ปี', '70-79' => '70–79 ปี', '80-89' => '80–89 ปี', '90+' => '90 ปีขึ้นไป'] as $v => $t)
                    <option value="{{ $v }}" @selected(request('age_range') === $v)>{{ $t }}</option>
                @endforeach
            </select>
        </label>
        <details class="more-filters" @if(request('created_date_from') || request('created_date_to')) open @endif>
            <summary class="btn btn-light">วันที่เพิ่มข้อมูล</summary>
            <div class="more-filters-body">
                <input type="date" name="created_date_from" class="form-control" value="{{ request('created_date_from') }}" aria-label="ตั้งแต่วันที่">
                <span>ถึง</span>
                <input type="date" name="created_date_to" class="form-control" value="{{ request('created_date_to') }}" aria-label="ถึงวันที่">
            </div>
        </details>
        <noscript><button type="submit" class="btn btn-primary">ค้นหา</button></noscript>
    </form>

    <div class="result-count">
        @if ($filtered)
            พบ {{ $elderlies->total() }} คนที่ตรงเงื่อนไข · <a href="{{ route('dashboard') }}">ล้างตัวกรอง</a>
        @else
            แสดง {{ $elderlies->count() }} จาก {{ $elderlies->total() }} คน
        @endif
    </div>

    <div class="table-responsive">
        <table class="table elder-table">
            <thead>
                <tr>
                    <th>ผู้สูงอายุ</th>
                    <th>กลุ่ม ADL</th>
                    <th>การประเมิน</th>
                    <th class="text-end"><span class="sr-only">จัดการ</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($elderlies as $e)
                    @php $adl = $e->barthel_adl; @endphp
                    <tr>
                        <td>
                            <a class="who" href="{{ route('elderly.profile', $e->ID_Elderly) }}">
                                <img src="{{ $e->image_url }}" alt="">
                                <span>
                                    <b>{{ $e->Name_Elderly }}</b>
                                    <small>{{ \Carbon\Carbon::parse($e->Birthday)->age }} ปี{{ $e->Phone_Elderly ? ' · ' . $e->Phone_Elderly : '' }}</small>
                                </span>
                            </a>
                        </td>
                        <td>
                            @if ($adl)
                                <span class="adl-cell">
                                    <span class="pill {{ $groupPill($adl->Group_ADL) }}">{{ str_replace('กลุ่ม', '', $adl->Group_ADL) }}</span>
                                    <small>{{ $adl->Score_ADL }} คะแนน</small>
                                </span>
                            @else
                                <span class="pill none">ยังไม่ทราบ</span>
                            @endif
                        </td>
                        <td>
                            {{-- TAI has no create page of its own: it opens right after an ADL is saved. --}}
                            <div class="checks">
                                @foreach ([['ADL', $adl, route('adl.create', ['elderly_id' => $e->ID_Elderly])],
                                           ['TAI', $e->score_tai, $adl ? route('tai.index') : route('adl.create', ['elderly_id' => $e->ID_Elderly])],
                                           ['CG', $e->care_giver, route('cg.create', ['elderly_id' => $e->ID_Elderly])]] as [$name, $has, $href])
                                    @if ($has)
                                        <span class="check ok" title="{{ $name }} บันทึกแล้ว"><span class="material-icons">check</span>{{ $name }}</span>
                                    @else
                                        <a class="check todo" href="{{ $href }}" title="ทำแบบประเมิน {{ $name }}"><span class="material-icons">add</span>{{ $name }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('elderly.profile', $e->ID_Elderly) }}" class="btn btn-light btn-sm">ดูข้อมูล</a>
                                <details class="app-pop">
                                    <summary class="btn btn-light btn-sm" aria-label="ตัวเลือกเพิ่มเติมของ {{ $e->Name_Elderly }}">
                                        <span class="material-icons">more_horiz</span>
                                    </summary>
                                    <div class="app-pop-panel">
                                        <a href="{{ route('edit-elderly', ['id' => $e->ID_Elderly]) }}"><span class="material-icons">edit</span>แก้ไขข้อมูล</a>
                                        <a href="{{ route('search-location', ['id' => $e->ID_Elderly]) }}" target="_blank" rel="noopener"><span class="material-icons">place</span>ดูที่อยู่บนแผนที่</a>
                                        <button type="button" class="text-danger" onclick="confirmDeleteElderly('{{ $e->ID_Elderly }}', @js($e->Name_Elderly))">
                                            <span class="material-icons">delete</span>ลบผู้สูงอายุ
                                        </button>
                                    </div>
                                </details>
                                <form action="{{ route('delete-elderly', ['id' => $e->ID_Elderly]) }}" method="POST"
                                    id="delete-elderly-form-{{ $e->ID_Elderly }}" hidden>@csrf @method('DELETE')</form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty">
                                <span class="material-icons">person_search</span>
                                @if ($filtered)
                                    ไม่พบผู้สูงอายุที่ตรงเงื่อนไข · <a href="{{ route('dashboard') }}">ล้างตัวกรอง</a>
                                @else
                                    ยังไม่มีข้อมูลผู้สูงอายุ · <a href="{{ route('add-elderly') }}">เพิ่มคนแรก</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($elderlies->hasPages())
        <div class="panel-pad">{{ $elderlies->links() }}</div>
    @endif
</section>

<div class="stats-grid">
    <section class="panel panel-pad">
        <h2 class="h-sub">ผู้สูงอายุตามช่วงอายุ</h2>
        <div class="chart-box"><canvas id="ageBarChart"></canvas></div>
        <canvas id="agePieChart" hidden></canvas>
    </section>
    <section class="panel panel-pad">
        <h2 class="h-sub">สัดส่วนกลุ่มสุขภาพ (ADL)</h2>
        <div class="chart-box"><canvas id="adlPieChart"></canvas></div>
        <canvas id="adlBarChart" hidden></canvas>
    </section>
</div>

<section class="panel panel-pad mt-4">
    <h2 class="h-sub">แผนที่บ้านผู้สูงอายุ</h2>
    @if (collect($elderlyLocations ?? [])->isEmpty())
        <p class="text-muted mb-0">ยังไม่มีผู้สูงอายุที่บันทึกพิกัดบ้าน · เพิ่มได้ที่ "แก้ไขข้อมูล" ของแต่ละคน</p>
        <div id="map" hidden></div>
    @else
        <div id="map" style="height: 420px; border-radius: 10px;"></div>
    @endif
</section>

@push('styles')
<style>
    .todo { padding: 18px 20px; margin-bottom: 20px; }
    .todo-head { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .todo-head h2, .h-sub { font-size: 19px; margin: 0; }
    .h-sub { margin-bottom: 12px; }
    .todo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
    .todo-item {
        display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 10px;
        border: 1px solid var(--line); text-decoration: none !important; color: var(--ink) !important; background: #fffaf0;
    }
    .todo-item:hover { border-color: var(--warn); }
    .todo-item > .material-icons:first-child { color: var(--warn); font-size: 28px; }
    .todo-item.done { background: var(--surface); color: var(--muted) !important; pointer-events: none; }
    .todo-item.done > .material-icons:first-child { color: var(--brand); }
    .todo-text { display: grid; gap: 2px; line-height: 1.3; }
    .todo-text small { font-size: 15px; color: var(--ink-2); }
    .todo-text b { font-size: 22px; }
    .todo-text b em { font-style: normal; font-size: 15px; font-weight: 600; }
    .todo-text b.none { font-size: 16px; color: var(--brand); }
    .adl-cell { display: inline-grid; justify-items: start; gap: 2px; }
    .adl-cell small { color: var(--muted); font-size: 14px; padding-left: 4px; }
    .checks { align-items: center; }
    .todo-go { margin-left: auto; color: var(--muted); }
    .more-filters { position: relative; }
    .more-filters summary { list-style: none; }
    .more-filters summary::-webkit-details-marker { display: none; }
    .more-filters-body {
        position: absolute; z-index: 20; top: calc(100% + 6px); right: 0; display: flex; gap: 8px; align-items: center;
        background: var(--surface); border: 1px solid var(--line); border-radius: 12px; padding: 12px; box-shadow: 0 12px 32px -12px rgba(0,0,0,.25);
    }
    .elder-table .who { text-decoration: none !important; color: var(--ink) !important; }
    .checks { display: flex; gap: 6px; flex-wrap: wrap; }
    .check { border: 1px solid transparent; display: inline-flex; align-items: center; gap: 2px; font-size: 14px; font-weight: 600; padding: 3px 10px 3px 6px; border-radius: 8px; }
    .check .material-icons { font-size: 18px; }
    .check.ok { background: var(--brand-soft); color: var(--brand-ink); }
    .check.todo { border: 1px dashed var(--warn); color: var(--warn) !important; text-decoration: none !important; }
    .check.todo:hover { background: var(--warn-soft); }
    .row-actions .app-pop > summary { padding: 4px 8px !important; }
    .row-actions .app-pop-panel { min-width: 220px; }
    .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
    .chart-box { position: relative; height: 240px; }
    @media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr; } }
    @media (max-width: 700px) {
        .elder-table thead { display: none; }
        .elder-table tr { display: grid; grid-template-columns: 1fr auto; gap: 8px 12px; padding: 14px 16px; border-top: 1px solid var(--line); }
        .elder-table td { border: 0 !important; padding: 0 !important; }
        .elder-table td:first-child { grid-column: 1 / -1; }
    }
</style>
@endpush

@push('scripts')
<script>
    // Filters apply as you choose them; search waits until you stop typing.
    (function () {
        const form = document.getElementById('elder-filter');
        let t;
        form.querySelectorAll('select, input[type=date]').forEach(el => el.addEventListener('change', () => form.submit()));
        const q = form.querySelector('input[name=search]');
        q.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => form.submit(), 600); });
        if (q.value) { q.focus(); q.setSelectionRange(q.value.length, q.value.length); }
    })();
    function confirmDeleteElderly(id, name) {
        Swal.fire({
            title: 'ลบข้อมูลของ ' + name + '?',
            text: 'ผลประเมินทั้งหมดของคนนี้จะถูกลบด้วย และกู้คืนไม่ได้',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'ลบข้อมูล', cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true
        }).then(r => { if (r.isConfirmed) document.getElementById('delete-elderly-form-' + id).submit(); });
    }
</script>
@endpush
