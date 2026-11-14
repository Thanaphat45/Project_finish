@extends('layouts.app')

@section('title', 'บันทึกการดูแล CG')

@php
    // Care-plan group from the TAI code stored on the CG row. B3/C*/I* mapping is the
    // original one; B4/B5 (normal) and plain group names used to fall through to
    // "not assessed", which was wrong, so they are now shown as they are.
    $groupOf = function ($code) {
        return match (true) {
            blank($code) => ['ยังไม่ได้ประเมิน', 'none'],
            $code === 'B3' => ['ติดบ้าน กลุ่มที่ 1', 'todo'],
            in_array($code, ['C4', 'C3', 'C2'], true) => ['ติดบ้าน กลุ่มที่ 2', 'todo'],
            in_array($code, ['I3', 'I2', 'I1'], true) => ['ติดเตียง กลุ่มที่ 1', 'bad'],
            in_array($code, ['B4', 'B5'], true) => ['กลุ่มปกติ', 'ok'],
            default => [$code, 'info'],
        };
    };
@endphp

@section('content')
    @foreach (['success' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls)
        @if (session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="page-head">
        <div>
            <h1>บันทึกการดูแล (CG)</h1>
            <p>ผลการปฏิบัติงานของผู้ดูแลผู้สูงอายุ (Care Giver)</p>
        </div>
        <div class="actions">
            <a href="{{ route('cg.export') }}" class="btn btn-light"><span class="material-icons">table_view</span>ส่งออก Excel</a>
            <a href="{{ route('report.all.cg') }}" target="_blank" rel="noopener" class="btn btn-light"><span class="material-icons">print</span>พิมพ์รายงาน</a>
            <a href="{{ route('cg.create') }}" class="btn btn-primary"><span class="material-icons">add</span>บันทึก CG</a>
        </div>
    </div>

    <section class="panel">
        <x-list-toolbar :action="route('cg.index')" :range="$range" :total="$careGivers->total()" placeholder="ชื่อผู้สูงอายุหรือผู้ดูแล" />

        <div class="table-responsive">
            <table class="table list-table">
                <thead>
                    <tr>
                        <th>ผู้สูงอายุ</th>
                        <th>ผู้ดูแล</th>
                        <th>กลุ่มการดูแล</th>
                        <th>วันที่</th>
                        <th><span class="sr-only">จัดการ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($careGivers as $cg)
                        @php [$groupName, $tone] = $groupOf($cg->Group_ADL); $filled = !is_null($cg->Name_CG); @endphp
                        <tr>
                            <td>
                                <a class="who" href="{{ route('elderly.profile', $cg->ID_Elderly) }}">
                                    <img src="{{ optional($cg->elderly)->image_url }}" alt="">
                                    <span><b>{{ $cg->Name_Elderly }}</b></span>
                                </a>
                            </td>
                            <td>{!! $filled ? e($cg->Name_CG) : '<span class="pill todo">ยังไม่ได้บันทึก</span>' !!}</td>
                            <td><span class="pill {{ $tone }}">{{ $groupName }}</span></td>
                            <td>{{ $cg->Date_CG ? \Carbon\Carbon::parse($cg->Date_CG)->locale('th')->translatedFormat('j M') . ' ' . (\Carbon\Carbon::parse($cg->Date_CG)->year + 543) : '–' }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('cg.edit', ['id' => $cg->ID_CG]) }}" @class(['btn', 'btn-sm', 'btn-light' => $filled, 'btn-primary' => !$filled])>{{ $filled ? 'แก้ไข' : 'บันทึกผล' }}</a>
                                    <details class="app-pop">
                                        <summary class="btn btn-light btn-sm" aria-label="ตัวเลือกเพิ่มเติม"><span class="material-icons">more_horiz</span></summary>
                                        <div class="app-pop-panel">
                                            <a href="{{ route('report.cg', ['id' => $cg->ID_CG]) }}" target="_blank" rel="noopener"><span class="material-icons">picture_as_pdf</span>ออกรายงาน PDF</a>
                                            <button type="button" class="text-danger" onclick="confirmDelete('delete-cg-form-{{ $cg->ID_CG }}', @js($cg->Name_Elderly))"><span class="material-icons">delete</span>ลบบันทึกนี้</button>
                                        </div>
                                    </details>
                                    <form id="delete-cg-form-{{ $cg->ID_CG }}" action="{{ route('cg.destroy', ['id' => $cg->ID_CG]) }}" method="POST" hidden>@csrf @method('DELETE')</form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><span class="material-icons">assignment</span>
                            @if (request()->hasAny(['search', 'date_from', 'date_to', 'date']) || $range !== 'all')
                                ไม่พบบันทึกที่ตรงเงื่อนไข · <a href="{{ route('cg.index') }}">ดูทั้งหมด</a>
                            @else
                                ยังไม่มีบันทึก CG · <a href="{{ route('cg.create') }}">บันทึกรายการแรก</a>
                            @endif
                        </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($careGivers->hasPages())<div class="panel-pad">{{ $careGivers->links() }}</div>@endif
    </section>
@endsection

@push('scripts')
    <script>
        function confirmDelete(formId, name) {
            Swal.fire({
                title: 'ลบบันทึก CG ของ ' + name + '?', text: 'ลบแล้วกู้คืนไม่ได้', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true
            }).then(r => { if (r.isConfirmed) document.getElementById(formId).submit(); });
        }
    </script>
@endpush
