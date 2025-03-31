@extends('app.layout')
@section('title') Listagem de Produtos @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Listagem de Produtos</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Listagem de Produtos</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <a href="{{ route('create-product') }}" class="btn btn-outline-primary">Novo Produto</a>
                <button type="button" class="btn btn-outline-primary" id="gerarExcel">Excel</button>
            </div>

            <div class="card p-2">
                <div class="card-body">
                    <h5 class="card-title">Produtos</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">Nome</th>
                                    <th class="text-center" scope="col">Valor</th>
                                    <th class="text-center" scope="col">T. Vendas</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">R$ {{ number_format($product->value, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $product->totalSale() }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('deleted-product') }}" method="POST" class="delete btn-group">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $product->id }}">
                                                <a href="{{ route('update-product', ['id' => $product->id]) }}" class="btn btn-warning text-light"><i class="bi bi-pencil-square"></i></a>
                                                <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

@endsection