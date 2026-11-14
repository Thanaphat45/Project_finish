@extends('layouts.app')

@section('title', 'บันทึกเยี่ยมบ้าน ACG')

@section('content')
    @foreach (['success' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls)
        @if (session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="page-head">
        <div>
            <h1>บันทึกเยี่ยมบ้าน (ACG)</h1>
            <p>กิจกรรมการดูแลผู้สูงอายุที่บ้าน (Activity of Care Giver)</p>
        </div>
        <div class="actions">
            <a href="{{ route('report.all.acg') }}" target="_blank" rel="noopener" class="btn btn-light"><span class="material-icons">print</span>พิมพ์รายงาน</a>
            <a href="{{ route('activities.create') }}" class="btn btn-primary"><span class="material-icons">add</span>บันทึกการเยี่ยม</a>
        </div>
    </div>

    <section class="panel">
        <x-list-toolbar :action="route('acg.index')" :range="$range" :total="$activities->total()" />

        <div class="table-responsive">
            <table class="table list-table">
                <thead>
                    <tr>
                        <th>ผู้สูงอายุ</th>
                        <th>ผู้ดูแล</th>
                        <th>ผลการประเมิน</th>
                        <th>วันที่เยี่ยม</th>
                        <th><span class="sr-only">จัดการ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        @php $cg = $activity->caregiver; $evaluated = !is_null($activity->Evaluate); @endphp
                        <tr>
                            <td><b>{{ $cg->Name_Elderly ?? '–' }}</b></td>
                            <td>{{ $cg->Name_CG ?? '–' }}</td>
                            <td>{!! $evaluated ? '<span class="pill ok">ประเมินแล้ว</span>' : '<span class="pill todo">ยังไม่ได้ประเมิน</span>' !!}</td>
                            <td>{{ $activity->Date_ACG ? \Carbon\Carbon::parse($activity->Date_ACG)->locale('th')->translatedFormat('j M') . ' ' . (\Carbon\Carbon::parse($activity->Date_ACG)->year + 543) : '–' }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('acg.edit', ['id' => $activity->ID_ACG]) }}" @class(['btn', 'btn-sm', 'btn-light' => $evaluated, 'btn-primary' => !$evaluated])>{{ $evaluated ? 'แก้ไข' : 'ประเมินผล' }}</a>
                                    <details class="app-pop">
                                        <summary class="btn btn-light btn-sm" aria-label="ตัวเลือกเพิ่มเติม"><span class="material-icons">more_horiz</span></summary>
                                        <div class="app-pop-panel">
                                            <a href="{{ route('report.acg', ['id' => $activity->ID_ACG]) }}" target="_blank" rel="noopener"><span class="material-icons">picture_as_pdf</span>ออกรายงาน PDF</a>
                                            <button type="button" class="text-danger" onclick="confirmDelete('delete-acg-form-{{ $activity->ID_ACG }}', @js($cg->Name_Elderly ?? ''))"><span class="material-icons">delete</span>ลบบันทึกนี้</button>
                                        </div>
                                    </details>
                                    <form id="delete-acg-form-{{ $activity->ID_ACG }}" action="{{ route('acg.destroy', ['id' => $activity->ID_ACG]) }}" method="POST" hidden>@csrf @method('DELETE')</form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><span class="material-icons">house</span>
                            @if (request()->hasAny(['search', 'date_from', 'date_to', 'date']) || $range !== 'all')
                                ไม่พบบันทึกที่ตรงเงื่อนไข · <a href="{{ route('acg.index') }}">ดูทั้งหมด</a>
                            @else
                                ยังไม่มีบันทึกการเยี่ยมบ้าน · <a href="{{ route('activities.create') }}">บันทึกครั้งแรก</a>
                            @endif
                        </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activities->hasPages())<div class="panel-pad">{{ $activities->links() }}</div>@endif
    </section>
@endsection

@push('scripts')
    <script>
        function confirmDelete(formId, name) {
            Swal.fire({
                title: 'ลบบันทึกการเยี่ยม' + (name ? 'ของ ' + name : '') + '?', text: 'ลบแล้วกู้คืนไม่ได้', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true
            }).then(r => { if (r.isConfirmed) document.getElementById(formId).submit(); });
        }
    </script>
@endpush
