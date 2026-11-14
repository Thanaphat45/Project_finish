{{--
    Home-visit (ACG) form, one page, shared by create and edit.
    $activity: ActivityCaregiver|null (edit). $elderlys: elders with a CG record (create only).
    Unticked activities are stored as "-" exactly as before (hidden input + checkbox with the same name).
--}}
@php
    $a = $activity ?? null;
    $health = [
        ['dress_the_wound', 'Dress_the_wound', 'ทำแผล'],
        ['rehabilitate', 'Rehabilitate', 'ฟื้นฟูสภาพฯ'],
        ['clean_body', 'Clean_body', 'ทำความสะอาดร่างกาย'],
        ['take_care_medicine', 'Take_care_medicine', 'ดูแลเรื่องยา'],
        ['take_care_feeding', 'Take_care_feeding', 'ดูแลให้อาหาร'],
        ['environmental', 'Environmental', 'การจัดสิ่งแวดล้อม'],
        ['take_exercise', 'Take_exercise', 'พาออกกำลังกาย'],
        ['give_advice_consult', 'Give_advice_consult', 'ให้คำแนะนำ/ปรึกษา'],
        ['take_to_see_a_doctor', 'Take_to_see_a_doctor', 'พาพบแพทย์'],
    ];
    $social = [
        ['take_to_make_merit', 'Take_to_make_merit', 'พาไปทำบุญ'],
        ['take_to_market', 'Take_to_market', 'พาไปจ่ายตลาด'],
        ['take_to_meet_friends', 'Take_to_meet_friends', 'พาไปพบเพื่อน'],
        ['take_to_allowance', 'Take_to_allowance', 'พาไปรับเบี้ย'],
        ['talk_as_friends', 'Talk_as_friends', 'พูดคุยเป็นเพื่อน'],
    ];
    $isOn = fn ($field, $col, $value) => old($field) !== null ? old($field) === $value : ($a && $a->{$col} === $value);
    $text = fn ($field, $col) => old($field, $a ? ($a->{$col} === '-' ? '' : $a->{$col}) : '');
@endphp

<section class="panel panel-pad acg-sec">
    <h2>1. การลงพื้นที่</h2>
    <div class="acg-grid">
        @if ($a)
            <label>ผู้สูงอายุ
                <input type="text" class="form-control" value="{{ $a->caregiver->Name_Elderly ?? '' }}" readonly>
            </label>
        @else
            <label><span>ผู้สูงอายุ <span class="req">*</span></span>
                <select name="ID_Elderly" class="form-control" required>
                    <option value="">เลือกผู้สูงอายุ</option>
                    @foreach ($elderlys as $e)
                        <option value="{{ $e->ID_Elderly }}" @selected(old('ID_Elderly', request('elderly_id')) == $e->ID_Elderly)>{{ $e->Name_Elderly }}</option>
                    @endforeach
                </select>
                @if ($elderlys->isEmpty())
                    <small class="hint">ยังไม่มีผู้สูงอายุที่มีบันทึก CG · ต้องบันทึก CG ก่อนจึงบันทึกการเยี่ยมได้</small>
                @endif
            </label>
        @endif
        <label><span>วันที่ลงพื้นที่ <span class="req">*</span></span>
            <input type="date" name="activity_date" class="form-control" required
                value="{{ old('activity_date', $a->Date_ACG ?? now()->toDateString()) }}">
        </label>
        <fieldset class="acg-type">
            <legend>ประเภทการเยี่ยม</legend>
            @foreach (['ประเมิน', 'ติดตามอาการ'] as $t)
                <label class="chip"><input type="radio" name="evaluate" value="{{ $t }}" @checked(old('evaluate', $a->Evaluate ?? null) === $t)><span>{{ $t }}</span></label>
            @endforeach
        </fieldset>
    </div>
</section>

<section class="panel panel-pad acg-sec">
    <h2>2. กิจกรรมด้านสาธารณสุข <small>เลือกได้หลายข้อ</small></h2>
    <div class="chips">
        @foreach ($health as [$field, $col, $value])
            <input type="hidden" name="{{ $field }}" value="-">
            <label class="chip"><input type="checkbox" name="{{ $field }}" value="{{ $value }}" @checked($isOn($field, $col, $value))><span>{{ $value }}</span></label>
        @endforeach
    </div>
    <label class="mt-3 d-block">อื่น ๆ (ถ้ามี)
        <input type="text" name="other_specified" class="form-control" value="{{ $text('other_specified', 'Other') }}">
    </label>
</section>

<section class="panel panel-pad acg-sec">
    <h2>3. กิจกรรมด้านสังคม <small>เลือกได้หลายข้อ</small></h2>
    <div class="chips">
        @foreach ($social as [$field, $col, $value])
            <input type="hidden" name="{{ $field }}" value="-">
            <label class="chip"><input type="checkbox" name="{{ $field }}" value="{{ $value }}" @checked($isOn($field, $col, $value))><span>{{ $value }}</span></label>
        @endforeach
    </div>
    <label class="mt-3 d-block">อื่น ๆ (ถ้ามี)
        <input type="text" name="other_social_specified" class="form-control" value="{{ $text('other_social_specified', 'Other_specified') }}">
    </label>
</section>

<section class="panel panel-pad acg-sec">
    <h2>4. ปัญหาและแนวทางแก้ไข</h2>
    <label class="d-block">ปัญหาที่พบ
        <textarea name="problem" class="form-control" rows="3">{{ $text('problem', 'Problem') }}</textarea>
    </label>
    <label class="d-block mt-3">แนวทางการแก้ไข
        <textarea name="solution" class="form-control" rows="3">{{ $text('solution', 'Solution') }}</textarea>
    </label>
</section>

<div class="score-bar">
    <div class="score-bar-inner">
        <span class="text-muted save-note">ช่องที่มี <span class="req">*</span> ต้องกรอก ที่เหลือเว้นได้</span>
        <a href="{{ route('acg.index') }}" class="btn btn-light">ยกเลิก</a>
        <button type="submit" class="btn btn-primary"><span class="material-icons">save</span>{{ $a ? 'บันทึกการแก้ไข' : 'บันทึกการเยี่ยม' }}</button>
    </div>
</div>

@push('styles')
<style>
    .acg-sec { margin-bottom: 16px; }
    .acg-sec h2 { font-size: 19px; margin: 0 0 14px; }
    .acg-sec h2 small { font-size: 14px; font-weight: 400; color: var(--muted); margin-left: 6px; }
    .acg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 16px; }
    .acg-grid > label { display: grid; gap: 6px; margin: 0; align-content: start; }
    .acg-type { grid-column: 1 / -1; border: 0; padding: 0; margin: 0; }
    .acg-type legend { font-size: 15px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; width: auto; float: none; }
    .req { color: var(--danger); }
    .hint { color: var(--warn); font-weight: 400; }
    .chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .chip { margin: 0 !important; cursor: pointer; }
    .chip input { position: absolute; opacity: 0; pointer-events: none; }
    .chip span {
        display: inline-flex; align-items: center; gap: 6px; min-height: 46px; padding: 8px 16px;
        border: 1.5px solid var(--line-2); border-radius: 999px; font-size: 16px; font-weight: 500; color: var(--ink); background: var(--surface);
    }
    .chip span::before { content: "+"; color: var(--muted); font-weight: 700; }
    .chip input:checked + span { background: var(--brand-soft); border-color: var(--brand); color: var(--brand-ink); font-weight: 700; }
    .chip input:checked + span::before { content: "✓"; color: var(--brand); }
    .chip input[type=radio] + span::before { content: none; }
    .chip input:focus-visible + span { outline: 3px solid #7fb89a; outline-offset: 2px; }
    .save-note { margin-right: auto; font-size: 15px; }
    .acg-sec:last-of-type { margin-bottom: 120px; }
    @media (max-width: 640px) {
        .acg-grid { grid-template-columns: 1fr; }
        .save-note { width: 100%; }
        .score-bar-inner .btn { flex: 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const form = document.querySelector('form[data-acg]');
        form.addEventListener('submit', () => {
            const btn = form.querySelector('[type=submit]');
            btn.disabled = true; btn.textContent = 'กำลังบันทึก…';
        });
    })();
</script>
@endpush
