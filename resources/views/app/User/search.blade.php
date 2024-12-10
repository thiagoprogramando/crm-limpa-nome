@extends('app.layout')
@section('title') Pesquisa: {{ $search }} @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Pesquisa: {{ $search }}</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Pesquisa: {{ $search }}</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">

        <div class="col-xxl-12 col-md-12 mb-3">
            <div class="card p-2">
                <h5 class="card-title">Vendas</h5>

                <div class="table-responsive">
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

        <div class="col-xxl-12 col-md-12 mb-3">
            <div class="card p-2">
                <h5 class="card-title">Pagamentos</h5>

                <div class="table-responsive">
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
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <th scope="row">{{ $invoice->id }}</th>
                                    <td>{{ $invoice->name }}</td>
                                    <td>{{ $invoice->description }}</td>
                                    <td class="text-center">R$ {{ $invoice->value }}</td>
                                    <td class="text-center">{{ $invoice->statusLabel() }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ $invoice->url_invoice }}" target="_blank" class="btn btn-success text-light">
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

        <div class="col-xxl-12 col-md-12 mb-3">
            <div class="card p-2">
                <h5 class="card-title">Listas</h5>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nome</th>
                                <th scope="col">Descrição</th>
                                <th class="text-center" scope="col">Encerramento</th>
                                <th class="text-center" scope="col">Status</th>
                                <th class="text-center" scope="col">Opções</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lists as $list)
                                <tr>
                                    <th scope="row">{{ $list->id }}</th>
                                    <td>{{ $list->name }}</td>
                                    <td>{{ $list->description }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($list->end)->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">{{ $list->statusLabel() }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('delete-list') }}" method="POST" class="delete">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $list->id }}">
                                            @if (Auth::user()->type == 1)
                                                <a href="{{ route('updatelist', ['id' => $list->id]) }}" class="btn btn-warning text-light"><i class="bi bi-pencil-square"></i></a>
                                                <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                            @endif
                                            <a href="{{ route('updatelist', ['id' => $list->id]) }}" class="btn btn-success text-light"><i class="bi bi-file-earmark-excel"></i></a>
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