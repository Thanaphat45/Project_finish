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
                    ['label' => 'จัดการ', 'class' => 'text-center']
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
                            <div class="row-actions justify-content-center">
                                <a href="{{ route('user.edit', $u->ID_User) }}" class="btn btn-light btn-sm">แก้ไข</a>
                                @if ($u->Type_Personnel !== 'Admin' || auth()->id() !== $u->ID_User)
                                    <button type="button" onclick="confirmDelete('{{ $u->ID_User }}', @js($u->Name_User ?: $u->Username))" class="btn btn-danger btn-sm">ลบ</button>
                                    <form id="delete-form-{{ $u->ID_User }}" action="{{ route('user.delete', $u->ID_User) }}" method="POST" hidden>
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
        @include('dashboard.doctor')

        @push('modals')
            <x-dashboard-doctor-modals :elderlys="$elderlys" />
        @endpush
    @endif
@endsection

@push('scripts')
    @if($role === 'Admin')
        <x-dashboard-admin-scripts />
        <script>     function confirmDelete(id, name) { Swal.fire({ title: 'ลบบัญชีของ ' + name + '?', text: 'ผู้ใช้นี้จะเข้าระบบไม่ได้อีก และกู้คืนไม่ได้', icon: 'warning', showCancelButton: true, confirmButtonText: 'ลบบัญชี', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#b42318', reverseButtons: true, focusCancel: true }).then((result) => { if (result.isConfirmed) document.getElementById('delete-form-' + id).submit(); }); }
        </script>
    @elseif($role === 'Staff')
        <x-dashboard-staff-scripts :ageGroups="$ageGroups" :adlGroups="$adlGroups" :elderlyLocations="$elderlyLocations" />
    @elseif($role === 'Doctor')
        <x-dashboard-doctor-scripts />
    @endif
@endpush