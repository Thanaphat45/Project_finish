{{--
    Search + date range toolbar for the staff result lists.
    Props: action (url), range (active key from App\Support\DateRange), total (int), placeholder.
--}}
@props(['action', 'range' => 'all', 'total' => 0, 'placeholder' => 'พิมพ์ชื่อ'])

<form method="GET" action="{{ $action }}" class="toolbar" data-list-filter>
    <label class="grow">ค้นหาชื่อผู้สูงอายุ
        <span class="search-box">
            <span class="material-icons">search</span>
            <input type="search" name="search" class="form-control" placeholder="{{ $placeholder }}" value="{{ request('search') }}" autocomplete="off">
        </span>
    </label>
    <div class="seg" role="group" aria-label="ช่วงเวลา">
        @foreach (\App\Support\DateRange::QUICK as $key => $label)
            <button type="submit" name="range" value="{{ $key }}" @class(['on' => $range === (string) $key]) aria-pressed="{{ $range === (string) $key ? 'true' : 'false' }}">{{ $label }}</button>
        @endforeach
    </div>
    <details class="more-filters" @if($range === 'custom') open @endif>
        <summary @class(['btn', 'btn-light', 'on' => $range === 'custom'])>กำหนดวันเอง</summary>
        <div class="more-filters-body">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" aria-label="ตั้งแต่วันที่">
            <span>ถึง</span>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" aria-label="ถึงวันที่">
            <button type="submit" class="btn btn-primary">ใช้</button>
        </div>
    </details>
    <input type="hidden" name="range" value="{{ $range === 'custom' ? 'all' : $range }}" data-keep-range disabled>
</form>

<div class="result-count">
    พบ {{ number_format($total) }} รายการ
    @if (request()->hasAny(['search', 'date_from', 'date_to']) || $range !== 'all')
        · <a href="{{ $action }}">ดูทั้งหมด</a>
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-list-filter]').forEach(form => {
                const q = form.querySelector('input[name=search]');
                const keep = form.querySelector('[data-keep-range]');
                const dates = form.querySelectorAll('input[type=date]');
                let t;
                // Text search runs when typing stops and keeps the range on screen.
                q.addEventListener('input', () => {
                    clearTimeout(t);
                    t = setTimeout(() => { keep.disabled = false; form.submit(); }, 600);
                });
                if (q.value) { q.focus(); q.setSelectionRange(q.value.length, q.value.length); }
                form.addEventListener('submit', e => {
                    // A quick-range button replaces any custom dates.
                    if (e.submitter && e.submitter.name === 'range') dates.forEach(d => d.disabled = true);
                });
            });
        </script>
    @endpush
@endonce
