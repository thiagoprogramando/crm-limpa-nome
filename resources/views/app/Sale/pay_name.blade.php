@extends('app.layout')
@section('title') Comprar Nomes em Lote @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Comprar Nomes em Lote</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Comprar Nomes em Lote</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal">Adicionar Fatura</button>
            </div>

            <div class="modal fade" id="updatedModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $sale->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Dados da venda</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    
                                </div>
                            </div>
                            <div class="modal-footer btn-group">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Histórico de Compras</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Descrição</th>
                                    <th scope="col">Vencimento</th>
                                    <th class="text-center" scope="col">Valor</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <th scope="row">{{ $invoice->id }}</th>
                                        <td>{{ $invoice->description }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $invoice->statusLabel() }}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-primary text-light"><i class="bi bi-upc"></i></a>
                                                @if(Auth::user()->type == 1)
                                                    <a href="{{ route('delete-invoice', ['id' => $invoice->id]) }}" class="btn btn-danger text-light confirm"><i class="bi bi-trash"></i></a>
                                                @endif
                                            </div>
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