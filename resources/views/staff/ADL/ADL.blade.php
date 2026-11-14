@extends('layouts.app')

@section('title', 'ประเมิน ADL')

@section('content')
    @php
        $fields = ['feeding', 'grooming', 'transfer', 'toilet_use', 'mobility', 'dressing', 'stairs', 'bathing', 'bowels', 'bladder'];
        $values = collect($fields)->mapWithKeys(fn ($f) => [$f => old($f)])->all();
        $selected = old('elderly_id', $selected_elderly_id ?? null);
    @endphp

    <div class="form-narrow">
        <a href="{{ route('adl.index') }}" class="back-link"><span class="material-icons">arrow_back</span>ผลประเมิน ADL</a>
        <div class="page-head">
            <div>
                <h1>ประเมิน ADL</h1>
                <p>ความสามารถในการทำกิจวัตรประจำวัน 10 ข้อ · เลือกคำตอบที่ตรงกับสภาพจริงมากที่สุด</p>
            </div>
        </div>

        @foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls)
            @if (session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
        @endforeach
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>บันทึกไม่สำเร็จ กรุณาตรวจสอบ:</strong>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('adl.submit') }}" id="adlForm">
            @csrf
            <section class="panel panel-pad who-panel">
                <label for="elderly_id">ผู้สูงอายุที่ประเมิน</label>
                <select name="elderly_id" id="elderly_id" class="form-control" required>
                    <option value="" disabled @selected(!$selected)>เลือกผู้สูงอายุ</option>
                    @foreach ($elderlies as $elderly)
                        @php $doneId = $assessedToday[$elderly->ID_Elderly] ?? null; @endphp
                        <option value="{{ $elderly->ID_Elderly }}" @selected($selected == $elderly->ID_Elderly)
                            @if ($doneId) data-edit="{{ route('adl.edit', ['id' => $doneId]) }}" @endif>
                            {{ $elderly->Name_Elderly }}{{ $doneId ? ' (ประเมินแล้ววันนี้)' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="already" id="already" hidden>
                    <span class="material-icons">info</span>
                    <span>คนนี้ได้รับการประเมิน ADL ไปแล้ววันนี้ (ประเมินได้วันละครั้ง) ·
                        <a id="already-link" href="#">แก้ไขผลของวันนี้แทน</a></span>
                </div>
                <p class="who-meta">ผู้ประเมิน: <b>{{ Auth::user()->Name_User }}</b> · วันที่ {{ now()->format('d/m/') }}{{ now()->year + 543 }}</p>
            </section>

            @include('staff.ADL._questions', [
                'values' => $values,
                'actions' => new \Illuminate\Support\HtmlString(
                    '<a href="' . e(route('adl.index')) . '" class="btn btn-light">ยกเลิก</a>' .
                    '<button type="submit" class="btn btn-primary"><span class="material-icons">save</span>บันทึกผลประเมิน</button>'
                ),
            ])
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .form-narrow { max-width: 860px; margin: 0 auto; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--muted) !important; text-decoration: none !important; margin-bottom: 8px; font-size: 15px; }
        .back-link .material-icons { font-size: 18px; }
        .who-panel { margin-bottom: 16px; display: grid; gap: 8px; }
        .who-panel label { font-size: 17px; color: var(--ink); }
        .who-meta { margin: 0; color: var(--muted); font-size: 15px; }
        .already { display: flex; gap: 8px; align-items: flex-start; padding: 12px 14px; border-radius: 10px; background: var(--warn-soft); color: var(--warn); font-size: 16px; }
        .already[hidden] { display: none; }
        .already a { font-weight: 700; color: var(--warn) !important; text-decoration: underline !important; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const sel = document.getElementById('elderly_id');
            const box = document.getElementById('already');
            const link = document.getElementById('already-link');
            const save = document.querySelector('#adlForm [type=submit]');
            function check() {
                const url = sel.selectedOptions[0] && sel.selectedOptions[0].dataset.edit;
                box.hidden = !url;
                if (url) link.href = url;
                if (save) save.disabled = !!url;
            }
            sel.addEventListener('change', check);
            check();
        })();
    </script>
@endpush
