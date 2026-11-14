@extends('layouts.app')

@section('title', 'บันทึกการเยี่ยมบ้าน')

@section('content')
    <div class="form-narrow">
        <a href="{{ route('acg.index') }}" class="back-link"><span class="material-icons">arrow_back</span>บันทึกเยี่ยมบ้าน (ACG)</a>
        <div class="page-head">
            <div>
                <h1>บันทึกการเยี่ยมบ้าน</h1>
                <p>กิจกรรมการดูแลที่ทำระหว่างลงพื้นที่ · ผู้สูงอายุต้องมีบันทึก CG ก่อน</p>
            </div>
        </div>

        @foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls)
            @if (session($key))<div class="alert alert-{{ $cls }}">{{ session($key) }}</div>@endif
        @endforeach
        @if ($errors->any())
            <div class="alert alert-danger"><strong>บันทึกไม่สำเร็จ:</strong>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('activities.store') }}" method="POST" data-acg>
            @csrf
            @include('staff.ACG._form', ['activity' => null, 'elderlys' => $elderlys])
        </form>
    </div>
@endsection
