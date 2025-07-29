@extends('app.layout')
@section('title') Carteira Digital: Internet Banking @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Carteira Digital: Internet Banking</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Carteira Digital: Internet Banking</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Solicitar saque</button>
                <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
            </div>

            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('wallet') }}" method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title">Dados da Pesquisa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="start" class="form-control" id="start" placeholder="Data inicial:">
                                            <label for="start">Data inicial:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="end" class="form-control" id="end" placeholder="Data final:">
                                            <label for="end">Data final:</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Consultar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="withdrawForm" action="{{ route('withdraw-send') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Preencha todos os dados!</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="key" class="form-control" id="key" placeholder="Informe a Chave Pix:" required>
                                            <label for="key">Chave Pix:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="value" class="form-control" id="value" placeholder="Informe o valor:" oninput="maskValue(this)" required>
                                            <label for="value">Valor:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <select name="type" class="form-select" id="type" required>
                                                <option selected="" value="">Tipo:</option>
                                                <option value="CPF">CPF</option>
                                                <option value="CNPJ">CNPJ</option>
                                                <option value="EMAIL">EMAIL</option>
                                                <option value="PHONE">Telefone:</option>
                                            </select>
                                            <label for="type">Tipo da chave</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Confirme sua senha:" required>
                                            <label for="floatingPassword">Confirme sua senha:</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                <button type="button" class="btn btn-primary" id="submitBtn">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-xxl-4 col-md-4">
                    <div class="card info-card revenue-card p-2">
                        <div class="card-body">
                            <h5 class="card-title">SALDO</h5>
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

                <div class="col-xxl-12 col-md-12">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-responsive table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th class="text-justify">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($extracts as $extract)
                                        <tr>
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
        </div>
    </div>
</section>

<script src="{{ asset('assets/js/wallet.js') }}"></script>
@endsection