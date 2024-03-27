@extends('app.layout')
@section('title') Gestão de Inadimplência @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Inadimplências</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Inadimplência</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card p-5">
                <div class="card-body">
                    <h5 class="card-title">Faturas com atrasos</h5>
    
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
                                        <form action="{{ route('delete-sale') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $invoice->id }}">
                                            <button type="submit" class="btn btn-success text-light"><i class="bi bi-whatsapp"></i></button>
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