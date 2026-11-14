{{--
    The 10 Thai Barthel ADL questions as large tap targets.
    $values: field => current score (for edit / old input); missing = unanswered.
--}}
@php
    // Wording is the system's original form text, unchanged.
    $questions = [
        ['feeding', 'การรับประทานอาหาร', null, [
            'ไม่สามารถตักอาหารเข้าปากได้ต้องมีคนป้อนให้',
            'ตักอาหารเองได้แต่ต้องมีคนช่วย',
            'ตักอาหารและช่วยตัวเองได้เป็นปกติ']],
        ['grooming', 'การดูแลร่างกาย', null, [
            'ต้องการความช่วยเหลือ',
            'ทำเองได้ (รวมทั้งที่ทำได้เองถ้าเตรียมอุปกรณ์ไว้ให้)']],
        ['transfer', 'การย้ายตัว (เช่น จากเตียงไปเก้าอี้)', null, [
            'นั่งไม่ได้ หรือต้องใช้คนสองคนช่วยกันยก',
            'ต้องใช้คนแข็งแรง 1 คนหรือคนทั่วไป 2 คนช่วยพยุง',
            'ต้องการคนช่วยพยุงเล็กน้อย หรือดูแลความปลอดภัย',
            'ทำได้เอง']],
        ['toilet_use', 'การใช้ห้องน้ำ (ขับถ่าย)', null, [
            'ช่วยตัวเองไม่ได้',
            'ทำเองได้บ้าง แต่ต้องการความช่วยเหลือบางส่วน',
            'ทำเองได้ดีเรียบร้อย']],
        ['mobility', 'การเคลื่อนที่ภายในบ้าน', null, [
            'เคลื่อนที่ไปไหนไม่ได้',
            'เคลื่อนที่ได้เองโดยใช้รถเข็น',
            'เดินได้แต่ต้องมีคนเดินช่วยพยุงดูแล',
            'เดินหรือเคลื่อนที่ได้เอง']],
        ['dressing', 'การสวมใส่เสื้อผ้า', null, [
            'ต้องมีคนช่วยสวมให้ทั้งหมด',
            'ช่วยตัวเองได้ครึ่งหนึ่ง ที่เหลือต้องมีคนช่วย',
            'ใส่เองได้ดีทั้งหมด']],
        ['stairs', 'การขึ้นลงบันได (1 ชั้น)', null, [
            'ไม่สามารถทำได้',
            'ต้องการคนช่วย',
            'ขึ้นลงได้เอง']],
        ['bathing', 'การอาบน้ำ', null, [
            'ต้องมีคนช่วยหรือทำให้',
            'อาบน้ำเองได้']],
        ['bowels', 'การกลั้นอุจจาระ', null, [
            'กลั้นไม่ได้ หรือต้องสวน',
            'กลั้นไม่ได้บางครั้ง',
            'กลั้นได้เป็นปกติ']],
        ['bladder', 'การกลั้นปัสสาวะ', null, [
            'กลั้นไม่ได้เลย',
            'กลั้นไม่ได้บางครั้ง',
            'กลั้นได้เป็นปกติ']],
    ];
@endphp

<ol class="q-list">
    @foreach ($questions as $i => [$name, $title, $hint, $choices])
        @php $current = $values[$name] ?? null; @endphp
        <li class="q" id="q-{{ $name }}" data-q="{{ $name }}">
            <fieldset>
                <legend>
                    <span class="q-no">{{ $i + 1 }}</span>
                    <span>{{ $title }}@if ($hint)<small>{{ $hint }}</small>@endif</span>
                </legend>
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

<div class="score-bar" role="status" aria-live="polite">
    <div class="score-bar-inner">
        <div class="sb-progress">
            <span>ตอบแล้ว <b id="sb-answered">0</b>/10 ข้อ</span>
            <span class="sb-track" aria-hidden="true"><i id="sb-fill"></i></span>
        </div>
        <div class="sb-score">
            คะแนน <b id="sb-score">0</b>/20
            <span id="sb-group" class="pill none">ยังตอบไม่ครบ</span>
        </div>
        <div class="sb-actions">
            {{ $actions ?? '' }}
        </div>
    </div>
</div>


@push('scripts')
<script>
    (function () {
        const qs = [...document.querySelectorAll('.q')];
        const groupOf = s => s <= 4 ? ['กลุ่มติดเตียง', 'bad'] : s <= 11 ? ['กลุ่มติดบ้าน', 'todo'] : ['กลุ่มติดสังคม', 'ok'];

        function update() {
            let answered = 0, score = 0;
            qs.forEach(q => {
                const c = q.querySelector('input:checked');
                q.classList.toggle('done', !!c);
                if (c) { answered++; score += +c.value; q.classList.remove('missing'); }
            });
            document.getElementById('sb-answered').textContent = answered;
            document.getElementById('sb-fill').style.width = (answered * 10) + '%';
            document.getElementById('sb-score').textContent = score;
            const g = document.getElementById('sb-group');
            if (answered === qs.length) { const [t, c] = groupOf(score); g.textContent = t; g.className = 'pill ' + c; }
            else { g.textContent = 'ยังตอบไม่ครบ'; g.className = 'pill none'; }
        }

        qs.forEach((q, i) => q.addEventListener('change', () => {
            update();
            // Move on to the next unanswered question, so a phone user can just keep tapping.
            const next = qs.slice(i + 1).find(n => !n.querySelector('input:checked'));
            if (next) setTimeout(() => next.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
        }));

        // On save: point at what is missing instead of a generic browser bubble.
        const form = qs[0] && qs[0].closest('form');
        if (form) {
            form.noValidate = true;
            form.addEventListener('submit', e => {
                const missingQ = qs.filter(q => !q.querySelector('input:checked'));
                const badField = [...form.elements].find(el => el.willValidate && !el.closest('.q') && !el.checkValidity());
                if (badField || missingQ.length) {
                    e.preventDefault();
                    missingQ.forEach(q => q.classList.add('missing'));
                    const target = badField || missingQ[0];
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    if (badField) badField.focus();
                    Swal.fire({
                        icon: 'info', confirmButtonText: 'ตกลง',
                        title: badField ? 'กรุณาเลือกผู้สูงอายุ' : 'ยังตอบไม่ครบ ' + missingQ.length + ' ข้อ',
                        text: badField ? '' : 'ข้อที่ยังไม่ได้ตอบมีกรอบสีแดง',
                    });
                    return;
                }
                const btn = form.querySelector('[type=submit]');
                if (btn) { btn.disabled = true; btn.textContent = 'กำลังบันทึก…'; }
            });
        }
        update();
    })();
</script>
@endpush
