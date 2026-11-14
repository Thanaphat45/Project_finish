@extends('layouts.app')

@section('title', 'ผลประเมิน ADL')

@php
    // Thai Barthel ADL index: 10 items, 20 points in total.
    $items = [
        ['Feeding', 'การรับประทานอาหาร', 2],
        ['Grooming', 'การล้างหน้า หวีผม แปรงฟัน', 1],
        ['Transfer', 'การลุกนั่งจากที่นอน', 3],
        ['Toilet_use', 'การใช้ห้องน้ำ', 2],
        ['Mobility', 'การเคลื่อนที่ภายในบ้าน', 3],
        ['Dressing', 'การสวมใส่เสื้อผ้า', 2],
        ['Stairs', 'การขึ้นลงบันได', 2],
        ['Bathing', 'การอาบน้ำ', 1],
        ['Bowels', 'การกลั้นอุจจาระ', 2],
        ['Bladder', 'การกลั้นปัสสาวะ', 2],
    ];
    $groupPill = fn ($g) => match ($g) {
        'กลุ่มติดเตียง' => 'bad', 'กลุ่มติดบ้าน' => 'todo', 'กลุ่มติดสังคม' => 'ok', default => 'none',
    };
@endphp

@section('content')
    @foreach (['success' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls)
        @if (session($key))
            <div class="alert alert-{{ $cls }}">{{ session($key) }}</div>
        @endif
    @endforeach

    <div class="page-head">
        <div>
            <h1>ผลประเมิน ADL</h1>
            <p>ความสามารถในการทำกิจวัตรประจำวัน (Barthel ADL) · คะแนนเต็ม 20</p>
        </div>
        <div class="actions">
            <a href="{{ route('adl.export') }}" class="btn btn-light"><span class="material-icons">table_view</span>ส่งออก Excel</a>
            <a href="{{ route('report.all.adl') }}" target="_blank" rel="noopener" class="btn btn-light"><span class="material-icons">print</span>พิมพ์รายงาน</a>
            <a href="{{ route('adl.create') }}" class="btn btn-primary"><span class="material-icons">add</span>ประเมิน ADL</a>
        </div>
    </div>

    <section class="panel">
        <x-list-toolbar :action="route('adl.index')" :range="$range" :total="$adls->total()" />

        <div class="table-responsive">
            <table class="table list-table">
                <thead>
                    <tr>
                        <th>ผู้สูงอายุ</th>
                        <th>ผลประเมิน</th>
                        <th>วันที่ประเมิน</th>
                        <th>ผู้ประเมิน</th>
                        <th><span class="sr-only">จัดการ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adls as $adl)
                        <tr>
                            <td>
                                <a class="who" href="{{ route('elderly.profile', $adl->ID_Elderly) }}">
                                    <img src="{{ optional($adl->elderly)->image_url }}" alt="">
                                    <span><b>{{ $adl->Name_Elderly }}</b></span>
                                </a>
                            </td>
                            <td>
                                <span class="score">
                                    <b>{{ $adl->Score_ADL }}</b><small>/20</small>
                                    <span class="pill {{ $groupPill($adl->Group_ADL) }}">{{ str_replace('กลุ่ม', '', $adl->Group_ADL) }}</span>
                                </span>
                            </td>
                            <td>{{ $adl->created_at?->locale('th')->translatedFormat('j M') }} {{ $adl->created_at ? $adl->created_at->year + 543 : '' }}</td>
                            <td>{{ $adl->Name_User }}</td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#adlModal-{{ $adl->ID_ADL }}">ดูรายละเอียด</button>
                                    <details class="app-pop">
                                        <summary class="btn btn-light btn-sm" aria-label="ตัวเลือกเพิ่มเติม"><span class="material-icons">more_horiz</span></summary>
                                        <div class="app-pop-panel">
                                            <a href="{{ route('adl.edit', ['id' => $adl->ID_ADL]) }}"><span class="material-icons">edit</span>แก้ไขผลประเมิน</a>
                                            <a href="{{ route('report.adl.pdf', ['id' => $adl->ID_ADL]) }}" target="_blank" rel="noopener"><span class="material-icons">picture_as_pdf</span>ออกรายงาน PDF</a>
                                            <button type="button" class="text-danger" onclick="confirmDelete('{{ $adl->ID_ADL }}', @js($adl->Name_Elderly))"><span class="material-icons">delete</span>ลบผลประเมินนี้</button>
                                        </div>
                                    </details>
                                    <form id="delete-adl-form-{{ $adl->ID_ADL }}" action="{{ route('adl.destroy', ['id' => $adl->ID_ADL]) }}" method="POST" hidden>@csrf @method('DELETE')</form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <span class="material-icons">assignment</span>
                                    @if ($range === 'today')
                                        วันนี้ยังไม่มีการประเมิน ADL · <a href="{{ route('adl.index') }}">ดูทั้งหมด</a>
                                    @elseif (request()->hasAny(['search', 'date_from', 'date_to']) || $range !== 'all')
                                        ไม่พบผลประเมินที่ตรงเงื่อนไข · <a href="{{ route('adl.index') }}">ดูทั้งหมด</a>
                                    @else
                                        ยังไม่มีผลประเมิน ADL · <a href="{{ route('adl.create') }}">เริ่มประเมินคนแรก</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($adls->hasPages())
            <div class="panel-pad">{{ $adls->links() }}</div>
        @endif
    </section>

    @foreach ($adls as $adl)
        <div class="modal fade" id="adlModal-{{ $adl->ID_ADL }}" tabindex="-1" aria-labelledby="adlModalLabel-{{ $adl->ID_ADL }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content adl-sheet">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title" id="adlModalLabel-{{ $adl->ID_ADL }}">{{ $adl->Name_Elderly }}</h2>
                            <p class="text-muted mb-0">ประเมินเมื่อ {{ $adl->created_at?->format('d/m/') }}{{ $adl->created_at ? $adl->created_at->year + 543 : '' }} โดย {{ $adl->Name_User }}</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="sheet-total">
                            <span><b>{{ $adl->Score_ADL }}</b>/20 คะแนน</span>
                            <span class="pill {{ $groupPill($adl->Group_ADL) }}">{{ $adl->Group_ADL }}</span>
                        </div>
                        <ul class="sheet-items">
                            @foreach ($items as $i => [$field, $label, $max])
                                @php $v = (int) $adl->{$field}; @endphp
                                <li>
                                    <span class="n">{{ $i + 1 }}</span>
                                    <span class="l">{{ $label }}</span>
                                    <span class="bar" aria-hidden="true"><i style="width: {{ $max ? ($v / $max) * 100 : 0 }}%"></i></span>
                                    <span class="v">{{ $v }}/{{ $max }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('adl.edit', ['id' => $adl->ID_ADL]) }}" class="btn btn-light">แก้ไข</a>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('styles')
    <style>
        .adl-sheet .modal-header { align-items: flex-start; }
        .adl-sheet .modal-title { font-size: 22px; margin: 0; }
        .sheet-total { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; font-size: 17px; }
        .sheet-total b { font-size: 32px; }
        .sheet-items { list-style: none; padding: 0; margin: 0; display: grid; gap: 4px; }
        .sheet-items li { display: grid; grid-template-columns: 28px 1fr 140px 48px; gap: 12px; align-items: center; padding: 8px 0; border-top: 1px solid var(--line); }
        .sheet-items .n { color: var(--muted); font-size: 14px; }
        .sheet-items .bar { height: 8px; background: var(--bg); border-radius: 4px; overflow: hidden; }
        .sheet-items .bar i { display: block; height: 100%; background: var(--brand); }
        .sheet-items .v { text-align: right; font-weight: 700; }
        @media (max-width: 640px) { .sheet-items li { grid-template-columns: 24px 1fr 48px; } .sheet-items .bar { display: none; } }
    </style>
@endpush

@push('scripts')
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'ลบผลประเมิน ADL ของ ' + name + '?', text: 'ลบแล้วกู้คืนไม่ได้', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true
            }).then(r => { if (r.isConfirmed) document.getElementById('delete-adl-form-' + id).submit(); });
        }
    </script>
@endpush
