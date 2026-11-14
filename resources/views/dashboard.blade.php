@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $role = auth()->user()->Type_Personnel;
    @endphp

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- 1. ADMIN DASHBOARD --}}
    @if($role === 'Admin')
        <x-card>
            <x-slot name="header">
                <h4 class="mb-0">จัดการข้อมูลผู้ใช้</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('user.register') }}" class="btn btn-primary d-flex align-items-center">
                        <i class="fas fa-plus me-2"></i> สร้างบัญชี
                    </a>
                    <button id="generate-pdf" class="btn btn-outline-success">
                        <i class="fas fa-print me-2"></i> พิมพ์รายงาน
                    </button>
                </div>
            </x-slot>

            <x-data-table id="userTable" :headers="[
                    ['label' => 'รูป', 'class' => 'text-center'],
                    ['label' => 'ชื่อ - นามสกุล', 'class' => 'text-center'],
                    ['label' => 'ชื่อผู้ใช้', 'class' => 'text-center'],
                    ['label' => 'ประเภทของผู้ใช้', 'class' => 'text-center'],
                    ['label' => 'ประเภทที่แพทย์ดูแล', 'class' => 'text-center'],
                    ['label' => 'อีเมล', 'class' => 'text-center'],
                    ['label' => 'Actions', 'class' => 'text-center']
                ]">
                @foreach ($users as $u)
                    <tr>
                        <td class="text-center">
                            <img src="{{ $u->image_url }}" alt="Avatar" class="avatar avatar-sm me-3 rounded-circle">
                        </td>
                        <td class="text-center">{{ $u->Name_User ?: 'ไม่มีข้อมูล' }}</td>
                        <td class="text-center">{{ $u->Username ?: 'ไม่มีข้อมูล' }}</td>
                        <td class="text-center">
                            <span
                                class="badge badge-sm bg-gradient-{{ $u->Type_Personnel == 'Admin' ? 'primary' : ($u->Type_Personnel == 'Staff' ? 'info' : 'success') }}">
                                @php $roles = ['Admin' => 'แอดมิน', 'Staff' => 'เจ้าหน้าที่', 'Doctor' => 'แพทย์']; @endphp
                                {{ $roles[$u->Type_Personnel] ?? $u->Type_Personnel }}
                            </span>
                        </td>
                        <td class="text-center">{{ $u->Type_Doctor ?: '-' }}</td>
                        <td class="text-center">{{ $u->Email ?: 'ไม่มีข้อมูล' }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('user.edit', $u->ID_User) }}" class="btn btn-link text-dark p-0 me-3"
                                    title="แก้ไข">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                @if ($u->Type_Personnel !== 'Admin' || auth()->id() !== $u->ID_User)
                                    <button onclick="confirmDelete('{{ $u->ID_User }}')" class="btn btn-link text-dark p-0" title="ลบ">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                    <form id="delete-form-{{ $u->ID_User }}" action="{{ route('user.delete', $u->ID_User) }}"
                                        method="POST" style="display:none;">
                                        @csrf @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>

        {{-- 2. STAFF DASHBOARD --}}
    @elseif($role === 'Staff')
        @include('dashboard.staff')

        {{-- 3. DOCTOR DASHBOARD --}}
    @elseif($role === 'Doctor')
        <div class="row mb-4">
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <x-stat-card title="ผู้ป่วยในการดูแลทั้งหมด" :value="$stats['total_patients']" icon="users" color="primary" />
            </div>
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <x-stat-card title="คำแนะนำที่รอการยืนยัน" :value="$stats['pending_ci']" icon="clock" color="warning" />
            </div>
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <x-stat-card title="ได้ทำการประเมินแล้ววันนี้" :value="$stats['today_assessed']" icon="check-circle"
                    color="success" percentage="+100%" description="จากเมื่อวาน" />
            </div>
        </div>

        <x-card>
            <x-slot name="header">
                <h4 class="mb-0">รายชื่อผู้สูงอายุที่ต้องประเมิน</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('care_instructions.index', ['unconfirmed' => 'true']) }}"
                        class="btn btn-outline-primary d-flex align-items-center">
                        <i class="fas fa-list-check me-2"></i> รายการที่รอยืนยัน
                    </a>
                </div>
            </x-slot>

            <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end px-3 pt-3 pb-2">
                <div class="col-md-3">
                    <label class="text-xs text-secondary mb-1">ช่วงอายุ</label>
                    <select name="age_range" class="form-control form-control-sm">
                        <option value="">ทั้งหมด</option>
                        <option value="60-69" {{ request('age_range') == '60-69' ? 'selected' : '' }}>60-69 ปี</option>
                        <option value="70-79" {{ request('age_range') == '70-79' ? 'selected' : '' }}>70-79 ปี</option>
                        <option value="80-89" {{ request('age_range') == '80-89' ? 'selected' : '' }}>80-89 ปี</option>
                        <option value="90+" {{ request('age_range') == '90+' ? 'selected' : '' }}>90 ปีขึ้นไป</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark mb-0">กรอง</button>
                    @if(request()->hasAny(['age_range']))
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary mb-0">ล้าง</a>
                    @endif
                </div>
            </form>

            <x-data-table id="doctorTable" :useDataTable="false" :headers="[
                    ['label' => 'รูป', 'class' => 'text-center'],
                    ['label' => 'ชื่อ-นามสกุล', 'class' => 'text-center'],
                    ['label' => 'อายุ', 'class' => 'text-center'],
                    ['label' => 'กลุ่ม ADL', 'class' => 'text-center'],
                    ['label' => 'คะแนน ADL', 'class' => 'text-center'],
                    ['label' => 'ผู้ดูแลล่าสุด', 'class' => 'text-center'],
                    ['label' => 'สถานะคำแนะนำ', 'class' => 'text-center'],
                    ['label' => 'การจัดการ', 'class' => 'text-center']
                ]">
                @forelse ($elderlys as $elderly)
                    <tr>
                        <td class="text-center">
                            <img src="{{ $elderly->image_url }}" alt="Elderly" class="avatar avatar-sm rounded-circle">
                        </td>
                        <td class="text-center">{{ $elderly->Name_Elderly }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($elderly->Birthday)->age }} ปี</td>
                        <td class="text-center">
                            <span
                                class="badge badge-sm bg-gradient-{{ optional($elderly->barthel_adl)->Group_ADL === 'กลุ่มติดเตียง' ? 'danger' : (optional($elderly->barthel_adl)->Group_ADL === 'กลุ่มติดบ้าน' ? 'warning' : 'success') }}">
                                {{ optional($elderly->barthel_adl)->Group_ADL ?: 'ยังไม่ประเมิน' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($elderly->barthel_adl)
                                <span class="badge badge-sm bg-info">{{ $elderly->barthel_adl->Score_ADL }} คะแนน</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($elderly->care_giver)
                                <div class="d-flex flex-column align-items-center">
                                    <span class="text-sm font-weight-bold">{{ $elderly->care_giver->Name_CG }}</span>
                                    <span
                                        class="text-xs text-muted">{{ \Carbon\Carbon::parse($elderly->care_giver->Date_CG)->format('d/m/Y') }}</span>
                                </div>
                            @else
                                <span class="text-muted small">ไม่มีข้อมูล</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $hasToday = $elderly->care_instructions->where('Date_CI', now()->toDateString())->isNotEmpty();
                                $hasPending = $elderly->care_instructions->whereNull('Confirm')->isNotEmpty();
                            @endphp

                            @if($hasToday)
                                <a href="{{ route('care_instructions.index', ['elderly_id' => $elderly->ID_Elderly]) }}"
                                    class="badge rounded-pill bg-gradient-success" style="cursor: pointer; text-decoration: none;">
                                    บันทึกแล้ววันนี้
                                </a>
                            @elseif($hasPending)
                                <a href="{{ route('care_instructions.index', ['elderly_id' => $elderly->ID_Elderly, 'unconfirmed' => 'true']) }}"
                                    class="badge rounded-pill bg-gradient-warning" style="cursor: pointer; text-decoration: none;">
                                    รอสตาฟยืนยัน
                                </a>
                            @else
                                <span class="text-muted small">ยังไม่มีคำแนะนำ</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-info me-2" data-toggle="modal"
                                    data-target="#adlModal-{{ $elderly->ID_Elderly }}" title="ดูรายละเอียด ADL">
                                    <i class="fas fa-file-medical"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info me-2" data-toggle="modal"
                                    data-target="#cgDatesModal-{{ $elderly->ID_Elderly }}" title="ดูรายละเอียด CG">
                                    <i class="fas fa-user-nurse"></i>
                                </button>
                                @if($hasToday)
                                    <a href="{{ route('care_instructions.create', ['elderly_id' => $elderly->ID_Elderly]) }}"
                                        class="btn btn-sm btn-secondary disabled" aria-disabled="true">
                                        <i class="fas fa-check me-1"></i> ให้คำแนะนำแล้ว
                                    </a>
                                @else
                                    <a href="{{ route('care_instructions.create', ['elderly_id' => $elderly->ID_Elderly]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-comment-medical me-1"></i> ให้คำแนะนำ
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">ไม่พบผู้สูงอายุที่ตรงกับเงื่อนไขที่กรอง</td>
                    </tr>
                @endforelse
            </x-data-table>
            <div class="px-3 pb-3">
                {{ $elderlys->links() }}
            </div>
        </x-card>

        @push('modals')
            <x-dashboard-doctor-modals :elderlys="$elderlys" />
        @endpush
    @endif
@endsection

@push('scripts')
    @if($role === 'Admin')
        <x-dashboard-admin-scripts />
        <script>     function confirmDelete(id) { Swal.fire({ title: 'ยืนยันการลบ?', text: "บัญชีนี้จะถูกลบถาวร!", icon: 'warning', showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#ea0606' }).then((result) => { if (result.isConfirmed) document.getElementById('delete-form-' + id).submit(); }); }
        </script>
    @elseif($role === 'Staff')
        <x-dashboard-staff-scripts :ageGroups="$ageGroups" :adlGroups="$adlGroups" :elderlyLocations="$elderlyLocations" />
    @elseif($role === 'Doctor')
        <x-dashboard-doctor-scripts />
    @endif
@endpush