@extends('layouts.app')

@section('title', $newsItem->title)

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .article { max-width: 820px; margin: 0 auto; }
        body.site .article { padding: 24px 20px 48px; }
        .article h1 { font-size: 30px; line-height: 1.35; margin: 4px 0 16px; }
        .article .ql-container.ql-snow { border: 0; font-family: 'Sarabun', sans-serif; font-size: 18px; }
        .article .ql-editor { padding: 0; line-height: 1.8; }
        .article-images { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin-top: 20px; }
        .article-images img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }
    </style>
@endpush

@section('content')
    <article class="article">
        <a href="{{ url('/') }}" class="back-link"><span class="material-icons">arrow_back</span>หน้าหลัก</a>
        <h1>{{ $newsItem->title }}</h1>
        <div class="panel panel-pad">
            <div id="editor-container">{!! $newsItem->content !!}</div>
            @if ($newsItem->images->isNotEmpty())
                <div class="article-images">
                    @foreach ($newsItem->images as $image)
                        <img src="{{ url('storage/' . $image->image_path) }}" alt="" loading="lazy">
                    @endforeach
                </div>
            @endif
        </div>
    </article>
@endsection

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        new Quill('#editor-container', { readOnly: true });
    </script>
@endpush
