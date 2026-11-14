@extends('layouts.app')

@section('title', 'รายงานการปฏิบัติงาน')

@section('content')
    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls)
        @if (session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="page-head">
        <div>
            <h1>รายงานการปฏิบัติงาน</h1>
            <p>บันทึกการลงพื้นที่ สภาวะของผู้สูงอายุ และกิจกรรมที่ทำ</p>
        </div>
        <div class="actions">
            <a href="{{ route('performanceReport.create') }}" class="btn btn-primary"><span class="material-icons">add</span>เพิ่มรายงาน</a>
        </div>
    </div>

    <section class="panel">
        <x-list-toolbar :action="route('performanceReport.index')" :range="$range" :total="$performanceReports->total()" />

        <div class="table-responsive">
            <table class="table list-table">
                <thead>
                    <tr>
                        <th>ผู้สูงอายุ</th>
                        <th>วันที่</th>
                        <th>สภาวะ</th>
                        <th>กิจกรรม</th>
                        <th>ผู้รายงาน</th>
                        <th><span class="sr-only">จัดการ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($performanceReports as $r)
                        @php $d = $r->Date ? \Carbon\Carbon::parse($r->Date) : null; @endphp
                        <tr>
                            <td>
                                @if ($r->elderly)
                                    <a class="who" href="{{ route('elderly.profile', $r->elderly->ID_Elderly) }}">
                                        <img src="{{ $r->elderly->image_url }}" alt="">
                                        <span><b>{{ $r->elderly->Name_Elderly }}</b>@if (optional($r->caregiver)->Name_CG)<small>ผู้ดูแล {{ $r->caregiver->Name_CG }}</small>@endif</span>
                                    </a>
                                @else
                                    <span class="text-muted">ไม่พบข้อมูลผู้สูงอายุ</span>
                                @endif
                            </td>
                            <td>{{ $d ? $d->locale('th')->translatedFormat('j M') . ' ' . ($d->year + 543) : '–' }}<small class="d-block text-muted">{{ $d?->format('H:i') }}</small></td>
                            <td>{{ $r->State ?: '–' }}</td>
                            <td class="pr-activity">{{ \Illuminate\Support\Str::limit($r->Activity, 80) ?: '–' }}</td>
                            <td>{{ optional($r->user)->Name_User ?? '–' }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('performanceReport.edit', $r->id) }}" class="btn btn-light btn-sm">แก้ไข</a>
                                    <details class="app-pop">
                                        <summary class="btn btn-light btn-sm" aria-label="ตัวเลือกเพิ่มเติม"><span class="material-icons">more_horiz</span></summary>
                                        <div class="app-pop-panel">
                                            <a href="{{ route('performanceReport.exportPDF', $r->id) }}" target="_blank" rel="noopener"><span class="material-icons">picture_as_pdf</span>ออกรายงาน PDF</a>
                                            <button type="button" class="text-danger" onclick="confirmDelete('{{ $r->id }}', @js(optional($r->elderly)->Name_Elderly ?? ''))"><span class="material-icons">delete</span>ลบรายงานนี้</button>
                                        </div>
                                    </details>
                                    <form id="delete-form-{{ $r->id }}" action="{{ route('performanceReport.destroy', $r->id) }}" method="POST" hidden>@csrf @method('DELETE')</form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty"><span class="material-icons">description</span>
                            @if (request()->hasAny(['search', 'date_from', 'date_to']) || $range !== 'all')
                                ไม่พบรายงานที่ตรงเงื่อนไข · <a href="{{ route('performanceReport.index') }}">ดูทั้งหมด</a>
                            @else
                                ยังไม่มีรายงาน · <a href="{{ route('performanceReport.create') }}">เพิ่มรายงานแรก</a>
                            @endif
                        </div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($performanceReports->hasPages())<div class="panel-pad">{{ $performanceReports->links() }}</div>@endif
    </section>
@endsection

@push('styles')
    <style>
        .pr-activity { max-width: 320px; white-space: normal; color: var(--ink-2); font-size: 15px !important; }
        .list-table .who small { display: block; }
    </style>
@endpush

@push('scripts')
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'ลบรายงาน' + (name ? 'ของ ' + name : '') + '?', text: 'ลบแล้วกู้คืนไม่ได้', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true
            }).then(r => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
        }
    </script>
@endpush
