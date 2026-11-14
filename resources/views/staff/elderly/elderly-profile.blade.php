@extends('layouts.app')

@section('title', 'ประวัติ ' . $elderly->Name_Elderly)

@section('content')
    <a href="{{ route('dashboard') }}" class="back-link"><span class="material-icons">arrow_back</span>ผู้สูงอายุในความดูแล</a>
    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls)
        @if (session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
    @endforeach
    <section class="panel panel-pad profile-head">
        <img src="{{ $elderly->image_url }}" alt="">
        <div class="profile-id">
            <h1>{{ $elderly->Name_Elderly }}</h1>
            <p>{{ $elderly->Gender }} · อายุ {{ \Carbon\Carbon::parse($elderly->Birthday)->age }} ปี{{ $elderly->Phone_Elderly ? ' · โทร ' . $elderly->Phone_Elderly : '' }}</p>
            @if ($elderly->Address)<p>{{ $elderly->Address }}</p>@endif
        </div>
        <div class="profile-actions">
            <a href="{{ route('adl.create', ['elderly_id' => $elderly->ID_Elderly]) }}" class="btn btn-primary"><span class="material-icons">accessibility_new</span>ประเมิน ADL</a>
            <a href="{{ route('cg.create', ['elderly_id' => $elderly->ID_Elderly]) }}" class="btn btn-light"><span class="material-icons">volunteer_activism</span>บันทึก CG</a>
            <a href="{{ route('edit-elderly', ['id' => $elderly->ID_Elderly]) }}" class="btn btn-light"><span class="material-icons">edit</span>แก้ไขข้อมูล</a>
        </div>
    </section>

    @push('styles')
        <style>
            .profile-head { display: flex; flex-wrap: wrap; align-items: center; gap: 20px; margin-bottom: 20px; }
            .profile-head img { width: 88px; height: 88px; border-radius: 50%; object-fit: cover; background: var(--bg); }
            .profile-id { flex: 1 1 260px; }
            .profile-id h1 { font-size: 28px; margin: 0 0 4px; }
            .profile-id p { margin: 0; color: var(--muted); }
            .profile-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        </style>
    @endpush

    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <x-card class="h-100">
                <div class="p-3">
                    <h6 class="text-secondary text-xs text-uppercase mb-2">สถานะ ADL ล่าสุด</h6>
                    @if ($elderly->barthel_adl)
                        <h5 class="mb-1">{{ $elderly->barthel_adl->Group_ADL }}</h5>
                        <p class="text-secondary mb-0">{{ $elderly->barthel_adl->Score_ADL }} คะแนน</p>
                    @else
                        <p class="text-muted mb-0">ยังไม่ได้ประเมิน</p>
                    @endif
                </div>
            </x-card>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <x-card class="h-100">
                <div class="p-3">
                    <h6 class="text-secondary text-xs text-uppercase mb-2">ข้อมูล CG ล่าสุด</h6>
                    @if ($elderly->care_giver)
                        <p class="mb-1">น้ำหนัก {{ $elderly->care_giver->Weight }} กก. · ส่วนสูง {{ $elderly->care_giver->Height }} ซม.</p>
                        <p class="text-secondary mb-0">{{ \Carbon\Carbon::parse($elderly->care_giver->Date_CG)->format('d/m/Y') }}</p>
                    @else
                        <p class="text-muted mb-0">ยังไม่มีข้อมูล</p>
                    @endif
                </div>
            </x-card>
        </div>
        <div class="col-md-4">
            <x-card class="h-100">
                <div class="p-3">
                    <h6 class="text-secondary text-xs text-uppercase mb-2">สถานะ TAI ล่าสุด</h6>
                    @if ($elderly->score_tai)
                        <h5 class="mb-0">{{ $elderly->score_tai->group }}</h5>
                    @else
                        <p class="text-muted mb-0">ยังไม่ได้ประเมิน</p>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">ประวัติการประเมิน ADL ({{ $elderly->adl_history->count() }} ครั้ง)</h6>
                </x-slot>
                @if ($elderly->adl_history->isEmpty())
                    <p class="text-muted p-3 mb-0">ยังไม่มีประวัติการประเมิน ADL</p>
                @else
                    <div class="p-3">
                        <canvas id="adlHistoryChart" style="max-height: 220px;"></canvas>
                    </div>
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">วันที่</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">คะแนน ADL</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">กลุ่ม ADL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($elderly->adl_history as $adl)
                                    <tr>
                                        <td class="text-center">{{ $adl->created_at->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ $adl->Score_ADL }}</td>
                                        <td class="text-center">{{ $adl->Group_ADL }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">ประวัติรายงานผู้ดูแล (CG) ({{ $elderly->cg_history->count() }} ครั้ง)</h6>
                </x-slot>
                @if ($elderly->cg_history->isEmpty())
                    <p class="text-muted p-3 mb-0">ยังไม่มีประวัติรายงาน CG</p>
                @else
                    <div class="p-3">
                        <canvas id="cgWeightChart" style="max-height: 220px;"></canvas>
                    </div>
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">วันที่</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">น้ำหนัก</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">ส่วนสูง</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">สัญญาณชีพ</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">ผู้รายงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($elderly->cg_history as $cg)
                                    <tr>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($cg->Date_CG)->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ $cg->Weight }} กก.</td>
                                        <td class="text-center">{{ $cg->Height }} ซม.</td>
                                        <td class="text-center">{{ $cg->Vital_signs }}</td>
                                        <td class="text-center">{{ $cg->Reporter }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <x-card>
                <x-slot name="header">
                    <h6 class="mb-0">ประวัติการประเมิน TAI ({{ $elderly->tai_history->count() }} ครั้ง)</h6>
                </x-slot>
                @if ($elderly->tai_history->isEmpty())
                    <p class="text-muted p-3 mb-0">ยังไม่มีประวัติการประเมิน TAI</p>
                @else
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">วันที่</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">กลุ่ม</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">Mobility</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">Confuse</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">Feed</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center">Toilet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($elderly->tai_history as $tai)
                                    <tr>
                                        <td class="text-center">{{ $tai->created_at->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ $tai->group }}</td>
                                        <td class="text-center">{{ $tai->mobility }}</td>
                                        <td class="text-center">{{ $tai->confuse }}</td>
                                        <td class="text-center">{{ $tai->feed }}</td>
                                        <td class="text-center">{{ $tai->toilet }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const adlHistory = @json($elderly->adl_history->sortBy('created_at')->values());
            const adlCanvas = document.getElementById('adlHistoryChart');
            if (adlCanvas && adlHistory.length > 0) {
                new Chart(adlCanvas, {
                    type: 'line',
                    data: {
                        labels: adlHistory.map(a => a.created_at.split('T')[0]),
                        datasets: [{
                            label: 'คะแนน ADL',
                            data: adlHistory.map(a => a.Score_ADL),
                            borderColor: '#1d6b46',
                            backgroundColor: 'rgba(29, 107, 70, 0.08)',
                            pointBackgroundColor: '#1d6b46',
                            tension: 0.3,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        // Full 0–20 scale so a 1–2 point change is not drawn as a collapse.
                        scales: { y: { min: 0, max: 20, ticks: { stepSize: 5 } } },
                    }
                });
            }

            const cgHistory = @json($elderly->cg_history->sortBy('Date_CG')->values());
            const cgCanvas = document.getElementById('cgWeightChart');
            if (cgCanvas && cgHistory.length > 0) {
                new Chart(cgCanvas, {
                    type: 'line',
                    data: {
                        labels: cgHistory.map(c => c.Date_CG),
                        datasets: [{
                            label: 'น้ำหนัก (กก.)',
                            data: cgHistory.map(c => c.Weight),
                            borderColor: 'rgba(45, 206, 137, 1)',
                            backgroundColor: 'rgba(45, 206, 137, 0.1)',
                            tension: 0.3,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                    }
                });
            }
        });
    </script>
@endpush
