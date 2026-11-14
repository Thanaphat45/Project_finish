{{--
    Elder add/edit form. $elderly: Elderly|null, $addressElderly: AddressElderly|null.
    Field names, the map picker and concatenateAddress() are unchanged.
--}}
@php $e = $elderly ?? null; @endphp

<section class="panel panel-pad el-sec">
    <h2>1. ข้อมูลส่วนตัว</h2>
    <div class="el-person">
        <div class="el-photo">
            <img id="image-preview" src="{{ $e ? $e->image_url : asset('images/avatar_other.svg') }}" alt="">
            <label class="btn btn-light btn-sm" for="Image_Elderly"><span class="material-icons">photo_camera</span>{{ $e ? 'เปลี่ยนรูป' : 'เพิ่มรูป' }}</label>
            <input type="file" id="Image_Elderly" name="Image_Elderly" accept="image/*" hidden onchange="previewImage(event)">
            <small>ไม่บังคับ</small>
        </div>
        <div class="el-fields">
            <label class="el-wide">ชื่อ-สกุล <span class="req">*</span>
                <input type="text" id="Name_Elderly" name="Name_Elderly" class="form-control" required
                    value="{{ old('Name_Elderly', $e->Name_Elderly ?? '') }}" autocomplete="off">
            </label>
            <label>เพศ <span class="req">*</span>
                <select id="Gender" name="Gender" class="form-control" required>
                    <option value="">เลือกเพศ</option>
                    @foreach (['ชาย', 'หญิง'] as $g)
                        <option value="{{ $g }}" @selected(old('Gender', $e->Gender ?? '') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </label>
            <label>เลขบัตรประชาชน
                <input type="text" id="ID_Card" name="ID_Card" class="form-control" maxlength="13" inputmode="numeric"
                    pattern="\d{13}" placeholder="13 หลัก (ไม่บังคับ)" value="{{ old('ID_Card', $e->ID_Card ?? '') }}">
            </label>
            <label>เบอร์โทรศัพท์ <span class="req">*</span>
                <input type="tel" id="Phone_Elderly" name="Phone_Elderly" class="form-control" required inputmode="tel"
                    value="{{ old('Phone_Elderly', $e->Phone_Elderly ?? '') }}">
            </label>
        </div>
    </div>
</section>

<section class="panel panel-pad el-sec">
    <h2>2. อายุ</h2>
    <p class="el-hint">ทราบวันเกิดให้เลือกวันเกิด ถ้าไม่ทราบแน่ชัดให้กรอกอายุ ระบบจะคำนวณวันเกิดโดยประมาณให้</p>
    <div class="el-age">
        <label>วันเกิด <span class="req">*</span>
            <input type="date" id="Birthday" name="Birthday" class="form-control" required max="{{ now()->toDateString() }}"
                value="{{ old('Birthday', $e->Birthday ?? '') }}" onchange="showAge()">
        </label>
        <span class="el-or">หรือ</span>
        <label>อายุ (ปี)
            <input type="number" id="Age_input" class="form-control" min="0" max="150" placeholder="เช่น 75"
                oninput="calculateBirthdayFromAge()">
        </label>
        <p class="el-age-out" id="age-out" aria-live="polite"></p>
    </div>
</section>

<section class="panel panel-pad el-sec">
    <h2>3. ที่อยู่และตำแหน่งบ้าน</h2>
    <p class="el-hint">ปักหมุดบนแผนที่เพื่อให้ทีมหาบ้านเจอเวลาลงพื้นที่</p>
    @if ($e)
        <x-map-picker :lat="$addressElderly->Latitude_position ?? null" :lng="$addressElderly->Longitude_position ?? null" :address="$e->Address" />
    @else
        <x-map-picker />
    @endif
</section>

<div class="score-bar">
    <div class="score-bar-inner">
        <span class="text-muted save-note">ช่องที่มี <span class="req">*</span> ต้องกรอก</span>
        <a href="{{ $e ? route('elderly.profile', $e->ID_Elderly) : route('dashboard') }}" class="btn btn-light">ยกเลิก</a>
        <button type="submit" class="btn btn-primary"><span class="material-icons">save</span>{{ $e ? 'บันทึกการแก้ไข' : 'บันทึกผู้สูงอายุ' }}</button>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 360px; width: 100%; margin-bottom: 16px; border-radius: 10px; }
        .el-sec { margin-bottom: 16px; }
        .el-sec:last-of-type { margin-bottom: 120px; }
        .el-sec h2 { font-size: 19px; margin: 0 0 14px; }
        .el-hint { color: var(--muted); margin: -8px 0 14px; font-size: 15px; }
        .req { color: var(--danger); }
        .el-person { display: grid; grid-template-columns: 140px 1fr; gap: 20px; align-items: start; }
        .el-photo { display: grid; justify-items: center; gap: 8px; }
        .el-photo img { width: 112px; height: 112px; border-radius: 50%; object-fit: cover; background: var(--bg); border: 1px solid var(--line); }
        .el-photo small { color: var(--muted); }
        .el-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 16px; }
        .el-fields label, .el-age label { display: block; margin: 0; }
        .el-fields label > .form-control, .el-age label > .form-control { margin-top: 6px; }
        .el-wide { grid-column: 1 / -1; }
        .el-age { display: grid; grid-template-columns: 1fr auto 1fr; gap: 12px; align-items: end; }
        .el-or { padding-bottom: 12px; color: var(--muted); }
        .el-age-out { grid-column: 1 / -1; margin: 0; color: var(--brand-ink); font-weight: 600; min-height: 1.5em; }
        .save-note { margin-right: auto; font-size: 15px; }
        @media (max-width: 640px) {
            .el-person, .el-fields { grid-template-columns: 1fr; }
            .el-age { grid-template-columns: 1fr; }
            .el-or { padding: 0; }
            .save-note { width: 100%; }
            .score-bar-inner .btn { flex: 1; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Many elders know their age but not their birthday: use this year minus age
        // (today's month/day) as an approximate birthday, so the age shown matches.
        function calculateBirthdayFromAge() {
            const age = parseInt(document.getElementById('Age_input').value, 10);
            if (isNaN(age) || age < 0) return;
            const today = new Date();
            const year = today.getFullYear() - age;
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById('Birthday').value = `${year}-${month}-${day}`;
            showAge(true);
        }

        function showAge(approx) {
            const v = document.getElementById('Birthday').value;
            const out = document.getElementById('age-out');
            if (!v) { out.textContent = ''; return; }
            const b = new Date(v), t = new Date();
            let age = t.getFullYear() - b.getFullYear();
            if (t.getMonth() < b.getMonth() || (t.getMonth() === b.getMonth() && t.getDate() < b.getDate())) age--;
            out.textContent = 'อายุ ' + age + ' ปี' + (approx ? ' (วันเกิดโดยประมาณ)' : '');
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => { document.getElementById('image-preview').src = reader.result; };
            reader.readAsDataURL(file);
        }

        document.addEventListener('DOMContentLoaded', () => {
            showAge();
            const form = document.querySelector('form[data-elder]');
            form.addEventListener('submit', e => {
                if (e.defaultPrevented) return;
                const btn = form.querySelector('[type=submit]');
                btn.disabled = true; btn.textContent = 'กำลังบันทึก…';
            });
        });
    </script>
@endpush
