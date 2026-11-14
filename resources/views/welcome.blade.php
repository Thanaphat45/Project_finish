@extends('layouts.app')

@section('title', 'ระบบประเมินผู้สูงอายุที่มีภาวะพึ่งพิง (ADL)')

@php
    $counts = [
        ['กลุ่มติดสังคม', $adlGroupCounts['กลุ่มติดสังคม'] ?? 0, 'ok', 'ช่วยเหลือตัวเองได้ดี'],
        ['กลุ่มติดบ้าน', $adlGroupCounts['กลุ่มติดบ้าน'] ?? 0, 'todo', 'ช่วยเหลือตัวเองได้บ้าง'],
        ['กลุ่มติดเตียง', $adlGroupCounts['กลุ่มติดเตียง'] ?? 0, 'bad', 'ต้องมีผู้ดูแลตลอด'],
    ];
    $total = collect($counts)->sum(fn ($c) => $c[1]);
@endphp

@section('content')
    <div class="home">
        @if ($sliders->isNotEmpty())
            <section class="slider" aria-roledescription="carousel" aria-label="ภาพประชาสัมพันธ์">
                <div class="slides" id="slides">
                    @foreach ($sliders as $slider)
                        <img src="{{ url('storage/' . $slider->image) }}" alt="" loading="lazy">
                    @endforeach
                </div>
                @if ($sliders->count() > 1)
                    <button type="button" class="slide-btn prev" onclick="plusSlides(-1)" aria-label="ภาพก่อนหน้า"><span class="material-icons">chevron_left</span></button>
                    <button type="button" class="slide-btn next" onclick="plusSlides(1)" aria-label="ภาพถัดไป"><span class="material-icons">chevron_right</span></button>
                @endif
            </section>
        @else
            <section class="hero panel">
                <h1>ระบบประเมินผู้สูงอายุที่มีภาวะพึ่งพิง</h1>
                <p>บันทึกและติดตามความสามารถในการทำกิจวัตรประจำวัน (Barthel ADL) การจำแนกกลุ่ม (TAI) การดูแลที่บ้าน และคำแนะนำจากแพทย์ ในที่เดียว</p>
                @guest
                    <a href="{{ url('login') }}" class="btn btn-primary">เข้าสู่ระบบ</a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">ไปที่งานของฉัน</a>
                @endguest
            </section>
        @endif

        <div class="home-grid">
            <section aria-labelledby="news-h">
                <h2 id="news-h" class="sec-h">ข่าวสารประชาสัมพันธ์</h2>
                @if ($news->isEmpty())
                    <div class="panel empty"><span class="material-icons">campaign</span>ยังไม่มีข่าวประชาสัมพันธ์</div>
                @else
                    <div class="news-grid">
                        @foreach ($news as $item)
                            @php $img = $item->images->first(); @endphp
                            <a class="news-card panel" href="{{ route('news.show', ['id' => $item->id]) }}">
                                @if ($img)
                                    <img src="{{ url('storage/' . $img->image_path) }}" alt="" loading="lazy">
                                @else
                                    <span class="news-noimg"><span class="material-icons">article</span></span>
                                @endif
                                <span class="news-title">{{ $item->title }}</span>
                                @if ($item->created_at)
                                    <small>{{ $item->created_at->locale('th')->translatedFormat('j M') }} {{ $item->created_at->year + 543 }}</small>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <aside aria-labelledby="stat-h">
                <h2 id="stat-h" class="sec-h">ผลการประเมิน ADL</h2>
                <div class="panel panel-pad stat">
                    <p class="stat-total"><b>{{ number_format($total) }}</b> ครั้ง</p>
                    <ul class="stat-list">
                        @foreach ($counts as [$label, $n, $tone, $hint])
                            <li>
                                <span class="pill {{ $tone }}">{{ $label }}</span>
                                <span class="stat-bar" aria-hidden="true"><i class="{{ $tone }}" style="width: {{ $total ? round($n / $total * 100) : 0 }}%"></i></span>
                                <b>{{ $n }}</b>
                                <small>{{ $hint }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>

        <section class="office panel panel-pad">
            <img src="{{ url('images/Logo.png') }}" alt="">
            <div>
                <h2>สำนักงานสาธารณสุขตัวอย่าง (Demo)</h2>
                <p>ตำบลตัวอย่าง อำเภอตัวอย่าง จังหวัดตัวอย่าง 10000 · โทร 080-000-0000 (ตัวอย่าง)</p>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .home { max-width: 1180px; margin: 0 auto; }
        body.site .home { padding: 24px 20px 48px; }
        .slider { position: relative; overflow: hidden; border-radius: var(--radius); margin-bottom: 24px; background: #dfe6e1; aspect-ratio: 16 / 5; }
        .slides { display: flex; height: 100%; transition: transform .5s ease; }
        .slides img { flex: 0 0 100%; width: 100%; height: 100%; object-fit: cover; }
        .slide-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border-radius: 50%; border: 0; background: rgba(255,255,255,.9); color: var(--ink); display: grid; place-items: center; cursor: pointer; }
        .slide-btn.prev { left: 12px; } .slide-btn.next { right: 12px; }
        .hero { padding: 32px; margin-bottom: 24px; }
        .hero h1 { font-size: 30px; margin: 0 0 8px; }
        .hero p { color: var(--ink-2); max-width: 60ch; margin: 0 0 18px; }
        .home-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
        .sec-h { font-size: 21px; margin: 0 0 12px; }
        .news-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 14px; }
        .news-card { display: grid; gap: 6px; padding: 0 0 14px; overflow: hidden; text-decoration: none !important; color: var(--ink) !important; }
        .news-card:hover { border-color: var(--brand); }
        .news-card img, .news-noimg { width: 100%; height: 150px; object-fit: cover; }
        .news-noimg { display: grid; place-items: center; background: var(--brand-soft); color: var(--brand); }
        .news-title, .news-card small { padding: 0 14px; }
        .news-title { font-weight: 700; font-size: 16px; line-height: 1.45; }
        .news-card small { color: var(--muted); }
        .stat-total { margin: 0 0 12px; color: var(--muted); }
        .stat-total b { font-size: 32px; color: var(--ink); }
        .stat-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 14px; }
        .stat-list li { display: grid; grid-template-columns: auto 1fr auto; gap: 4px 10px; align-items: center; }
        .stat-list small { grid-column: 1 / -1; color: var(--muted); font-size: 14px; }
        .stat-bar { height: 8px; background: var(--bg); border-radius: 4px; overflow: hidden; }
        .stat-bar i { display: block; height: 100%; }
        .stat-bar i.ok { background: var(--brand); } .stat-bar i.todo { background: #d89a2b; } .stat-bar i.bad { background: var(--danger); }
        .office { display: flex; align-items: center; gap: 16px; margin-top: 28px; }
        .office img { height: 56px; }
        .office h2 { font-size: 18px; margin: 0; }
        .office p { margin: 2px 0 0; color: var(--muted); }
        @media (max-width: 900px) { .home-grid { grid-template-columns: 1fr; } .slider { aspect-ratio: 16 / 7; } }
    </style>
@endpush

@push('scripts')
    <script>
        let slideIndex = 0;
        const slides = document.getElementById('slides');
        function plusSlides(n) {
            if (!slides) return;
            const total = slides.children.length;
            slideIndex = (slideIndex + n + total) % total;
            slides.style.transform = `translateX(${-slideIndex * 100}%)`;
        }
        if (slides && slides.children.length > 1 && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setInterval(() => plusSlides(1), 5000);
        }
    </script>
@endpush
