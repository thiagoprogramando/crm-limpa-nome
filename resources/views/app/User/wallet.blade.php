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

        <div class="col-xxl-3 col-md-6">
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

        <div class="col-xxl-3 col-md-6">
            <div class="card info-card wallet-card p-2">
                <div class="card-body">
                    <h5 class="card-title">CARTEIRA DE INVESTIDOR</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-tablet-landscape"></i>
                        </div>
                        <div class="ps-3">
                            <h6>R$ {{ number_format(Auth::user()->wallet_off, 2, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
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

        <div class="col-xxl-3 col-md-6">
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
            <div class="card p-2">

                <div class="btn-group mb-3 w-25" role="group">
                    @if(Auth::user()->level == 7 || Auth::user()->level == 9)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#depModal">Depositar investimento</button>
                    @endif
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>

                <div class="modal fade" id="depModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('create-deposit') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Preencha os dados</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Informe o Valor:" oninput="mascaraReal(this)" required>
                                                <label for="floatingValue">Valor:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Confirme sua senha:" required>
                                                <label for="floatingPassword">Confirme sua senha:</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-success">Gerar Depósito</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <h5 class="card-title">Extrato</h5>

                <div class="table-responsive">
                    <table class="table table-responsive table-hover" id="table">
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