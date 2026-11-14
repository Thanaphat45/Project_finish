@extends('layouts.app')

@section('title', 'แก้ไขข้อมูลผู้สูงอายุ')

@section('content')
    <div class="form-narrow">
        <a href="{{ route('elderly.profile', $elderly->ID_Elderly) }}" class="back-link"><span class="material-icons">arrow_back</span>{{ $elderly->Name_Elderly }}</a>
        <div class="page-head"><div><h1>แก้ไขข้อมูลผู้สูงอายุ</h1></div></div>

        <x-validation-errors />

        <form action="{{ route('update-elderly', ['id' => $elderly->ID_Elderly]) }}" method="POST" enctype="multipart/form-data" onsubmit="concatenateAddress()" data-elder>
            @csrf
            @method('PUT')
            @include('staff.elderly._form', ['elderly' => $elderly, 'addressElderly' => $addressElderly])
        </form>
    </div>
@endsection
