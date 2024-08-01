@extends('app.layout')
@section('title') Minhas Compras @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Minhas Compras</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Modo Cal Center</a></li>
                <li class="breadcrumb-item active">Minhas Compras</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>
                
                <div class="card p-5">
                    <div class="card-body">
                        <h5 class="card-title">Produtos</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th class="text-center" scope="col">Valor</th>
                                        <th class="text-center" scope="col">Data compra</th>
                                        <th class="text-center" scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sales as $sale)
                                        <tr>
                                            <th scope="row">{{ $sale->id }}</th>
                                            <td><a href="{{ route('my-product', ['id' => $sale->id_product]) }}">{{ $sale->product->name }}</a></td>
                                            <td class="text-center">R$ {{ number_format($sale->value, 2, ',', '.') }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ $sale->statusLabel() }}</td>
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