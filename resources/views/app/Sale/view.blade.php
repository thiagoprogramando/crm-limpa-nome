@extends('app.layout')
@section('title') Dados da Venda: N° {{ $sale->id }} - {{ $sale->user->name }} @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Dados da Venda: N° {{ $sale->id }} - {{ $sale->user->name }}</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Modo Cal Center</a></li>
            <li class="breadcrumb-item active">Dados da Venda: N° {{ $sale->id }}</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Faturas associadas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">N°</th>
                                    <th scope="col">Descrição</th>
                                    <th scope="col">Vencimento</th>
                                    <th class="text-center" scope="col">V. Parcela</th>
                                    <th class="text-center" scope="col">V. Comissão</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <th scope="row">{{ $invoice->num }}</th>
                                        <td>{{ $invoice->description }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                        <td class="text-center">R$ {{ number_format($invoice->commission, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $invoice->statusLabel() }}</td>
                                        <td class="text-center">
                                            <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-primary text-light"><i class="bi bi-upc"></i></a>
                                            <a href="{{ route('send-default-whatsapp', ['id' => $invoice->id]) }}" class="btn btn-success text-light"><i class="bi bi-whatsapp"></i></a>
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