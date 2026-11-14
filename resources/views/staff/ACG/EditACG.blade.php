@extends('layouts.app')

@section('title', 'แก้ไขการเยี่ยมบ้าน')

@section('content')
    <div class="form-narrow">
        <a href="{{ route('acg.index') }}" class="back-link"><span class="material-icons">arrow_back</span>บันทึกเยี่ยมบ้าน (ACG)</a>
        <div class="page-head">
            <div>
                <h1>แก้ไขการเยี่ยมบ้าน</h1>
                <p>{{ $activity->caregiver->Name_Elderly ?? '' }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><strong>บันทึกไม่สำเร็จ:</strong>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('acg.update', ['id' => $activity->ID_ACG]) }}" method="POST" data-acg>
            @csrf
            @method('PATCH')
            @include('staff.ACG._form', ['activity' => $activity])
        </form>
    </div>
@endsection
