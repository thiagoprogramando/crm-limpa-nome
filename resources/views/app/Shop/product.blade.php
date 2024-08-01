@extends('app.layout')
@section('title') {{ $product->name }} @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>{{ $product->name }}</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Modo Cal Center</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Material Associado</h5>
                    
                    <div class="list-group">
                        @foreach ($itens as $item)
                            <a target="_blank" href="@if($item->type != 1) {{ asset("storage/{$item->item}") }} @else # @endif" class="list-group-item list-group-item-action" aria-current="true">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">
                                        @switch($item->type)
                                            @case(3)
                                                <i class="bi bi-camera-video me-1 text-primary"></i> 
                                                @break
                                            @case(2)
                                                <i class="bi bi-book me-1 text-primary"></i> 
                                                @break
                                            @case(4)
                                                <i class="bi bi-link me-1 text-primary"></i> 
                                                @break
                                            @default
                                                <i class="bi bi-journal-text me-1 text-primary"></i> 
                                                @break
                                        @endswitch
                                        {{ $item->name }}
                                    </h5>
                                    <small>3 days ago</small>
                                </div>
                                <p class="mb-1">{{ $item->description }}</p>
                            </a>
                        @endforeach
                    </div> 

                </div>
            </div>
        </div>
    </div>
</section>

@endsection