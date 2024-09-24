@extends('client.app.layout')
@section('title') Faturas @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Faturas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app.cliente') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Faturas</li>
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
                        <h5 class="card-title">Faturas</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Descrição</th>
                                        <th class="text-center" scope="col">Valor</th>
                                        <th class="text-center" scope="col">Status</th>
                                        <th class="text-center" scope="col">Vencimento</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        <tr>
                                            <th scope="row">{{ $invoice->id }}</th>
                                            <td>{{ $invoice->name }}</td>
                                            <td>{{ $invoice->description }}</td>
                                            <td class="text-center">R$ {{ $invoice->value }}</td>
                                            <td class="text-center">{{ $invoice->statusLabel() }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                            <td class="text-center">
                                                <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-success text-light">
                                                    <i class="bi bi-credit-card"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            {{ $invoices->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection