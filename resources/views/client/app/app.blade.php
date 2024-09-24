@extends('client.app.layout')
@section('title') Minhas Compras @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Minhas Compras</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app.cliente') }}">Escritório</a></li>
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
                        <h5 class="card-title">Vendas</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">N°</th>
                                        <th scope="col">Lista</th>
                                        <th scope="col">Produto</th>
                                        <th scope="col">Vendedor</th>
                                        <th class="text-center" scope="col">Status - Data</th>
                                        <th class="text-center" scope="col">Status Serasa</th>
                                        <th class="text-center" scope="col">Status SPC</th>
                                        <th class="text-center" scope="col">Status Boa Vista</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sales as $sale)
                                        <tr>
                                            <th scope="row">{{ $sale->id }}</th>
                                            <th>{{ $sale->list->name }}</th>
                                            <td title="{{ $sale->product->name }}">{{ $sale->product->name }}</td>
                                            <td>{{ $sale->user->name }}</td>
                                            <td class="text-center">{{ $sale->statusLabel() }} - {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</td>
                                            <td class="text-center">{{ $sale->list->serasa_status }}</td>
                                            <td class="text-center">{{ $sale->list->status_spc }}</td>
                                            <td class="text-center">{{ $sale->list->status_boa_vista }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                    <a href="{{ $sale->url_contract }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-file-earmark-text"></i></a>
                                                    <a href="{{ route('invoice.cliente', ['sale' => $sale->id]) }}" class="btn btn-outline-primary"><i class="bi bi-currency-dollar"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            {{ $sales->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection