@extends('layouts.app')

@section('title', 'ผลประเมิน TAI')

@php
    // TAI classification codes → plain-language group.
    $groupOf = function ($code) {
        return match (true) {
            in_array($code, ['B5', 'B4', 'B3'], true) => ['กลุ่มปกติ', 'ok'],
            in_array($code, ['C4', 'C3', 'C2'], true) => ['กลุ่มติดบ้าน', 'todo'],
            in_array($code, ['I3', 'I2', 'I1'], true) => ['กลุ่มติดเตียง', 'bad'],
            default => [null, 'none'],
        };
    };
@endphp

@section('content')
    @foreach (['success' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls)
        @if (session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="page-head">
        <div>
            <h1>ผลประเมิน TAI</h1>
            <p>การจำแนกกลุ่มผู้สูงอายุตามความสามารถ (Thai Activity Index) · ทำต่อหลังบันทึก ADL</p>
        </div>
        <div class="actions">
            <a href="{{ route('tai.export') }}" class="btn btn-light"><span class="material-icons">table_view</span>ส่งออก Excel</a>
            <a href="{{ route('report.all.tai') }}" target="_blank" rel="noopener" class="btn btn-light"><span class="material-icons">print</span>พิมพ์รายงาน</a>
        </div>
    </div>

    <section class="panel">
        <x-list-toolbar :action="route('tai.index')" :range="$range" :total="$tai->total()" />

        <div class="table-responsive">
            <table class="table list-table">
                <thead>
                    <tr>
                        <th>ผู้สูงอายุ</th>
                        <th>ผลประเมิน</th>
                        <th>อัปเดตล่าสุด</th>
                        <th>ผู้ประเมิน</th>
                        <th><span class="sr-only">จัดการ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tai as $item)
                        @php [$groupName, $tone] = $groupOf($item->group); @endphp
                        <tr>
                            <td>
                                @if ($item->elderly)
                                    <a class="who" href="{{ route('elderly.profile', $item->elderly->ID_Elderly) }}">
                                        <img src="{{ $item->elderly->image_url }}" alt="">
                                        <span><b>{{ $item->elderly->Name_Elderly }}</b></span>
                                    </a>
                                @else
                                    <span class="text-muted">ไม่พบข้อมูลผู้สูงอายุ</span>
                                @endif
                            </td>
                            <td>
                                @if ($groupName)
                                    <span class="score"><b>{{ $item->group }}</b><span class="pill {{ $tone }}">{{ $groupName }}</span></span>
                                @else
                                    <span class="pill todo">ยังไม่ได้ประเมิน</span>
                                @endif
                            </td>
                            <td>{{ $item->updated_at?->locale('th')->translatedFormat('j M') }} {{ $item->updated_at ? $item->updated_at->year + 543 : '' }}</td>
                            <td>{{ $item->user->Name_User ?? '–' }}</td>
                            <td>
                                <div class="row-actions">
                                    @if ($groupName)
                                        <a href="{{ route('tai.edit', ['id' => $item->id]) }}" class="btn btn-light btn-sm">แก้ไข</a>
                                    @else
                                        <a href="{{ route('tai.edit', ['id' => $item->id]) }}" class="btn btn-primary btn-sm">ทำแบบประเมิน</a>
                                    @endif
                                    <details class="app-pop">
                                        <summary class="btn btn-light btn-sm" aria-label="ตัวเลือกเพิ่มเติม"><span class="material-icons">more_horiz</span></summary>
                                        <div class="app-pop-panel">
                                            <a href="{{ route('report.tai.pdf', ['id' => $item->id]) }}" target="_blank" rel="noopener"><span class="material-icons">picture_as_pdf</span>ออกรายงาน PDF</a>
                                            <button type="button" class="text-danger" onclick="confirmDelete('{{ $item->id }}', @js(optional($item->elderly)->Name_Elderly ?? ''))"><span class="material-icons">delete</span>ลบผลประเมินนี้</button>
                                        </div>
                                    </details>
                                    <form id="delete-tai-form-{{ $item->id }}" action="{{ route('tai.destroy', ['id' => $item->id]) }}" method="POST" hidden>@csrf @method('DELETE')</form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <span class="material-icons">assignment</span>
                                    @if (request()->hasAny(['search', 'date_from', 'date_to']) || $range !== 'all')
                                        ไม่พบผลประเมินที่ตรงเงื่อนไข · <a href="{{ route('tai.index') }}">ดูทั้งหมด</a>
                                    @else
                                        ยังไม่มีผลประเมิน TAI · TAI จะเริ่มหลังบันทึก ADL · <a href="{{ route('adl.create') }}">ประเมิน ADL</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tai->hasPages())
            <div class="panel-pad">{{ $tai->links() }}</div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'ลบผลประเมิน TAI' + (name ? ' ของ ' + name : '') + '?', text: 'ลบแล้วกู้คืนไม่ได้', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true
            }).then(r => { if (r.isConfirmed) document.getElementById('delete-tai-form-' + id).submit(); });
        }
    </script>
@endpush
