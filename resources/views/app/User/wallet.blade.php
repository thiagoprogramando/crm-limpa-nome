@extends('app.layout')
@section('title') Carteira Digital @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Carteira Digital</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Carteira Digital</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">

        <div class="col-xxl-4 col-md-6">
            <div class="card info-card revenue-card p-2">
                <div class="card-body">
                    <h5 class="card-title">DISPONÍVEL PARA SAQUE</h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="ps-3">
                            <h6>R$ {{ number_format($balance, 2, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card info-card sales-card p-2">

                <div class="card-body">
                    <h5 class="card-title">RECEBÍVEIS</h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-bank"></i>
                        </div>
                        <div class="ps-3">
                            <h6>R$ {{ number_format($statistics, 2, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card info-card customers-card p-2">
                <div class="card-body">
                    <h5 class="card-title">ACUMULADO</h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-piggy-bank"></i>
                        </div>
                        <div class="ps-3">
                            <h6>R$ {{ number_format($accumulated, 2, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-12 col-md-12">
            <div class="card p-5">
                <h5 class="card-title">Extrato</h5>

                <div class="table-responsive">
                    <table class="table table-responsive table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th class="text-justify">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($extracts as $extract)
                                <tr>
                                    <td>{{ $extract['id'] }}</td>
                                    <td>
                                        @if($extract['value'] < 0)
                                            Saída
                                        @else
                                            Entrada
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($extract['date'])->format('d/m/Y') }}</td>
                                    <td>{{ $extract['description'] }}</td>
                                    <td class="text-justify">R$ {{ number_format($extract['value'], 2, ',', '.') }}</td>
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