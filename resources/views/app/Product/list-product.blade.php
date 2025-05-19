@extends('app.layout')
@section('title') Listagem de Produtos @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Listagem de Produtos</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Listagem de Produtos</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <a href="{{ route('create-product') }}" class="btn btn-sm btn-outline-primary">Novo Produto</a>
            </div>

            <div class="card">
                <div class="card-body m-0 p-0">
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">Nome</th>
                                    <th class="text-center" scope="col">Valores</th>
                                    <th class="text-center" scope="col">T. Vendas</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">
                                            <span class="badge border border-secondary border-1 text-secondary">Mín. {{ number_format($product->value_min, 2, ',', '.') }}</span>
                                            <span class="badge border border-secondary border-1 text-secondary">Max. {{ number_format($product->value_max, 2, ',', '.') }} </span>
                                        </td>
                                        <td class="text-center">{{ $product->totalSale() }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('deleted-product') }}" method="POST" class="delete btn-group">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $product->id }}">
                                                <a href="{{ route('update-product', ['id' => $product->id]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
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