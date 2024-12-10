@extends('app.layout')
@section('title') Pagamentos @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Pagamentos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Extrato de Pagamentos</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>

                <div class="card p-2">
                    <div class="card-body">
                        <h5 class="card-title">Pagamentos</h5>
                        
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
                                    @foreach ($payments as $payment)
                                        <tr>
                                            <th scope="row">{{ $payment->id }}</th>
                                            <td>{{ $payment->name }}</td>
                                            <td>{{ $payment->description }}</td>
                                            <td class="text-center">R$ {{ $payment->value }}</td>
                                            <td class="text-center">{{ $payment->statusLabel() }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</td>
                                            <td class="text-center btn-group">
                                                <a href="{{ route('payMonthly', ['id' => $payment->id]) }}" class="btn btn-success text-light">
                                                    <i class="bi bi-credit-card"></i> Pagar com saldo
                                                </a>
                                                <a href="{{ $payment->url_payment }}" target="_blank" class="btn btn-primary text-light">
                                                    <i class="bi bi-arrow-up-right-circle"></i>
                                                </a>
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