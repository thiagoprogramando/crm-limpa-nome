@extends('app.layout')
@section('title') Material de Apoio @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Material de Apoio</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Material de Apoio</li>
            </ol>
        </nav>
    </div>
    
    <section class="dashboard">
        <div class="card p-3">
            <iframe class="embed-responsive-item w-100" width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PLESFJATdYslhRdJSw-6k7rXG39dK8Yxn4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen> </iframe>
        </div>
    </section>
@endsection