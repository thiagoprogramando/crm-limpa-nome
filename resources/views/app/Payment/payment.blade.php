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
                <div class="card p-5">
                    <div class="card-body">
                        <h5 class="card-title">Pagamentos</h5>
        
                        <table class="table table-hover">
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
                                        <td class="text-center">
                                            <a href="{{ $payment->url_payment }}" target="_blank" class="btn btn-success text-light">
                                                <i class="bi bi-credit-card"></i>
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
    </section>
@endsection