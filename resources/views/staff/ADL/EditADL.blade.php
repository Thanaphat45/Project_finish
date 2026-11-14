@extends('layouts.app')

@section('title', 'แก้ไขผล ADL')

@section('content')
    @php
        // DB columns are capitalised; the form fields are lower-case.
        $map = ['feeding' => 'Feeding', 'grooming' => 'Grooming', 'transfer' => 'Transfer', 'toilet_use' => 'Toilet_use',
                'mobility' => 'Mobility', 'dressing' => 'Dressing', 'stairs' => 'Stairs', 'bathing' => 'Bathing',
                'bowels' => 'Bowels', 'bladder' => 'Bladder'];
        $values = collect($map)->mapWithKeys(fn ($col, $f) => [$f => old($f, $adl->{$col})])->all();
    @endphp

    <div class="form-narrow">
        <a href="{{ route('adl.index') }}" class="back-link"><span class="material-icons">arrow_back</span>ผลประเมิน ADL</a>
        <div class="page-head">
            <div>
                <h1>แก้ไขผล ADL</h1>
                <p>{{ $adl->Name_Elderly }} · ประเมินเมื่อ {{ $adl->created_at?->format('d/m/') }}{{ $adl->created_at ? $adl->created_at->year + 543 : '' }} โดย {{ $adl->Name_User }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>บันทึกไม่สำเร็จ กรุณาตรวจสอบ:</strong>
                <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('adl.update', ['id' => $adl->ID_ADL]) }}" id="adlForm">
            @csrf
            @method('PATCH')
            <input type="hidden" name="elderly_id" value="{{ $adl->ID_Elderly }}">

            @include('staff.ADL._questions', [
                'values' => $values,
                'actions' => new \Illuminate\Support\HtmlString(
                    '<a href="' . e(route('adl.index')) . '" class="btn btn-light">ยกเลิก</a>' .
                    '<button type="submit" class="btn btn-primary"><span class="material-icons">save</span>บันทึกการแก้ไข</button>'
                ),
            ])
        </form>
    </div>
@endsection
