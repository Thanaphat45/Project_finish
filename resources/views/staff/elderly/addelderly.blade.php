@extends('layouts.app')

@section('title', 'เพิ่มผู้สูงอายุ')

@section('content')
    <div class="form-narrow">
        <a href="{{ route('dashboard') }}" class="back-link"><span class="material-icons">arrow_back</span>ผู้สูงอายุในความดูแล</a>
        <div class="page-head"><div><h1>เพิ่มผู้สูงอายุ</h1><p>บันทึกข้อมูลพื้นฐานก่อน แล้วค่อยทำแบบประเมิน ADL ต่อ</p></div></div>

        <x-validation-errors />

        <form action="{{ route('store-elderly') }}" method="POST" enctype="multipart/form-data" onsubmit="concatenateAddress()" data-elder>
            @csrf
            @include('staff.elderly._form', ['elderly' => null, 'addressElderly' => null])
        </form>
    </div>
@endsection
