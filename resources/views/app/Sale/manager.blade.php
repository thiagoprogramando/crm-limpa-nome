@extends('app.layout')
@section('title') Gestão de Vendas @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Vendas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Vendas</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card p-5">
                <div class="card-body">
                    <h5 class="card-title">Vendas</h5>
    
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">N°</th>
                                <th scope="col">Produto</th>
                                <th scope="col">Cliente</th>
                                <th class="text-center" scope="col">V. Venda</th>
                                <th class="text-center" scope="col">V. Comissão</th>
                                <th class="text-center" scope="col">Status</th>
                                <th class="text-center" scope="col">Processo</th>
                                <th class="text-center" scope="col">Opções</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sales as $sale)
                                <tr>
                                    <th scope="row">{{ $sale->id }}</th>
                                    <td>{{ $sale->product->name }}</td>
                                    <td>{{ $sale->user->name }}</td>
                                    <td class="text-center">R$ {{ number_format($sale->value, 2, ',', '.') }}</td>
                                    <td class="text-center">R$ {{ number_format($sale->commission, 2, ',', '.') }}</td>
                                    <td class="text-center">{{ $sale->statusLabel() }}</td>
                                    <td class="text-center">{{ $sale->label }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('delete-sale') }}" method="POST" class="delete">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $sale->id }}">
                                            <a href="{{ route('update-sale', ['id' => $sale->id]) }}" class="btn btn-warning text-light"><i class="bi bi-arrow-up-right-circle"></i></a>
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
</section>

@endsection