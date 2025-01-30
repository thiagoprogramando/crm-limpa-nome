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
            <iframe class="embed-responsive-item" width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PLESFJATdYslhRdJSw-6k7rXG39dK8Yxn4" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen> </iframe>

            <div class="list-group mt-5">
                <button type="button" class="list-group-item list-group-item-action active" aria-current="true">
                    Links Úteis
                </button>
                <a href="https://drive.google.com/file/d/1a5ilCLvx10jXZefxX-9_mCdzxybSoZgq/view?usp=sharing" target="_blank" class="list-group-item list-group-item-action">Criativos para Post Limpa nome!🎁🧑‍🎄</a>
            </div>
        </div>
    </section>
@endsection