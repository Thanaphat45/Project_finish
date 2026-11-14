@extends('layouts.app')

@section('title', 'แบบประเมิน TAI')

@section('content')
    @php
        // Option wording is the system's original text, unchanged.
        $questions = [
            ['mobility', 'การเคลื่อนไหว (Mobility)', [
                'ไม่สามารถเคลื่อนไหวเองได้', 'Roll over', 'สามารถยืนได้บางครั้ง',
                'สามารถเดินเองได้ในระยะสั้น', 'สามารถเดินเองได้ในระยะยาว', 'สามารถเคลื่อนไหวได้อย่างปกติ']],
            ['confuse', 'ความสับสน (Confuse)', [
                'สับสนมากไม่สามารถทำกิจกรรมได้', 'สับสนเล็กน้อย', 'สามารถทำกิจกรรมบางอย่างได้',
                'ทำกิจกรรมได้บางประการ', 'สามารถทำกิจกรรมได้เกือบทั้งหมด', 'ทำกิจกรรมได้ตามปกติ']],
            ['feed', 'การรับประทานอาหาร (Feeding)', [
                'ไม่สามารถทานอาหารเองได้', 'ทานอาหารได้บ้างแต่ต้องการความช่วยเหลือ', 'ทานอาหารได้บางประเภทเองได้',
                'ทานอาหารได้เกือบทุกประเภทเองได้', 'ทานอาหารเองได้ทุกประเภท', 'ทานอาหารเองได้ทั้งหมดอย่างไม่มีปัญหา']],
            ['toilet', 'การใช้ห้องน้ำ (Toilet)', [
                'ไม่สามารถใช้ห้องน้ำเองได้', 'ต้องการความช่วยเหลือในการใช้ห้องน้ำ', 'สามารถใช้ห้องน้ำได้แต่ต้องการความช่วยเหลือบางอย่าง',
                'สามารถใช้ห้องน้ำได้เองเกือบทั้งหมด', 'สามารถใช้ห้องน้ำเองได้ทั้งหมด', 'สามารถใช้ห้องน้ำเองได้โดยไม่มีปัญหา']],
        ];
    @endphp

    <div class="form-narrow">
        <a href="{{ route('tai.index') }}" class="back-link"><span class="material-icons">arrow_back</span>ผลประเมิน TAI</a>
        <div class="page-head">
            <div>
                <h1>แบบประเมิน TAI</h1>
                <p>{{ $elderly->Name_Elderly }} · ผู้ประเมิน {{ $user->Name_User }} · 4 ข้อ ข้อละ 0–5 คะแนน</p>
            </div>
        </div>

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>บันทึกไม่สำเร็จ กรุณาตรวจสอบ:</strong>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tai.update', $tai->id) }}" id="taiForm">
            @csrf
            @method('patch')

            <ol class="q-list">
                @foreach ($questions as $i => [$name, $title, $choices])
                    @php $current = old($name, $tai->{$name}); @endphp
                    <li class="q" data-q="{{ $name }}">
                        <fieldset>
                            <legend><span class="q-no">{{ $i + 1 }}</span><span>{{ $title }}</span></legend>
                            <div class="q-choices">
                                @foreach ($choices as $score => $text)
                                    <label class="choice">
                                        <input type="radio" name="{{ $name }}" value="{{ $score }}" required
                                            @checked($current !== null && (string) $current === (string) $score)>
                                        <span class="choice-text">{{ $text }}</span>
                                        <span class="choice-score">{{ $score }} คะแนน</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </li>
                @endforeach
            </ol>
            <input type="hidden" name="group" id="group_code_input">

            <div class="score-bar" role="status" aria-live="polite">
                <div class="score-bar-inner">
                    <div class="sb-progress">
                        <span>ตอบแล้ว <b id="sb-answered">0</b>/4 ข้อ</span>
                        <span class="sb-track" aria-hidden="true"><i id="sb-fill"></i></span>
                    </div>
                    <div class="sb-score">
                        กลุ่ม <b id="group_code">–</b>
                        <span id="group" class="pill none">ยังตอบไม่ครบ</span>
                    </div>
                    <div class="sb-actions">
                        <a href="{{ route('tai.index') }}" class="btn btn-light">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary"><span class="material-icons">save</span>บันทึกผลประเมิน</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // TAI grouping rules: copied unchanged from the previous form.
        function taiGroup(mobility, confuse, feed, toilet) {
            if (mobility === 5 && confuse === 5 && feed === 5 && toilet === 5) return 'B5';
            if (mobility >= 3 && confuse >= 4 && feed >= 4 && toilet >= 4) return 'B4';
            if (mobility >= 3 && confuse >= 4 && feed <= 3 && toilet <= 3) return 'B3';
            if (mobility >= 3 && confuse <= 3 && feed >= 4 && toilet >= 4) return 'C4';
            if (mobility >= 3 && confuse <= 3 && feed === 3 && toilet === 4) return 'C3';
            if (mobility >= 3 && confuse <= 3 && feed === 4 && toilet === 3) return 'C3';
            if (mobility >= 3 && confuse <= 3 && feed <= 3 && toilet <= 3) return 'C2';
            if (mobility <= 2 && feed >= 4) return 'I3';
            if (mobility <= 2 && feed === 3) return 'I2';
            if (mobility <= 2 && feed <= 2) return 'I1';
            return '';
        }

        (function () {
            const form = document.getElementById('taiForm');
            const qs = [...form.querySelectorAll('.q')];
            const val = n => { const c = form.querySelector(`input[name="${n}"]:checked`); return c ? +c.value : null; };

            function update() {
                let answered = 0;
                qs.forEach(q => { const done = !!q.querySelector('input:checked'); q.classList.toggle('done', done); if (done) { answered++; q.classList.remove('missing'); } });
                document.getElementById('sb-answered').textContent = answered;
                document.getElementById('sb-fill').style.width = (answered * 25) + '%';

                const pill = document.getElementById('group');
                let code = '';
                if (answered === qs.length) {
                    code = taiGroup(val('mobility'), val('confuse'), val('feed'), val('toilet'));
                    const [text, tone] = code.startsWith('B') ? ['กลุ่มปกติ', 'ok'] : code.startsWith('C') ? ['กลุ่มติดบ้าน', 'todo']
                        : code.startsWith('I') ? ['กลุ่มติดเตียง', 'bad'] : ['คำตอบชุดนี้ไม่เข้าเกณฑ์กลุ่มใด', 'bad'];
                    pill.textContent = text; pill.className = 'pill ' + tone;
                } else {
                    pill.textContent = 'ยังตอบไม่ครบ'; pill.className = 'pill none';
                }
                document.getElementById('group_code').textContent = code || '–';
                document.getElementById('group_code_input').value = code;
            }

            qs.forEach((q, i) => q.addEventListener('change', () => {
                update();
                const next = qs.slice(i + 1).find(n => !n.querySelector('input:checked'));
                if (next) setTimeout(() => next.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
            }));

            form.noValidate = true;
            form.addEventListener('submit', e => {
                const missing = qs.filter(q => !q.querySelector('input:checked'));
                if (missing.length) {
                    e.preventDefault();
                    missing.forEach(q => q.classList.add('missing'));
                    missing[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    Swal.fire({ icon: 'info', title: 'ยังตอบไม่ครบ ' + missing.length + ' ข้อ', text: 'ข้อที่ยังไม่ได้ตอบมีกรอบสีแดง', confirmButtonText: 'ตกลง' });
                    return;
                }
                if (!document.getElementById('group_code_input').value) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'จัดกลุ่มไม่ได้', text: 'คำตอบชุดนี้ไม่ตรงกับเกณฑ์กลุ่ม TAI ใดเลย กรุณาทบทวนคำตอบอีกครั้ง', confirmButtonText: 'ตกลง' });
                    return;
                }
                const btn = form.querySelector('[type=submit]');
                btn.disabled = true; btn.textContent = 'กำลังบันทึก…';
            });
            update();
        })();
    </script>
@endpush
