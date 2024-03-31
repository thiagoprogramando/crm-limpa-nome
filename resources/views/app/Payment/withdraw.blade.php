@extends('app.layout')
@section('title') Saques @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Saques</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Saques</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Solicitar saque</button>
                    <button type="button" class="btn btn-outline-primary">Excel</button>
                </div>

                <div class="modal fade" id="filterModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('withdraw-send') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Preencha todos os dados!</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <input type="text" name="key" class="form-control" id="floatingKey" placeholder="Informe a Chave Pix:" required>
                                                <label for="floatingKey">Chave Pix:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-1">
                                            <div class="form-floating">
                                                <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Informe o valor:" oninput="mascaraReal(this)" required>
                                                <label for="floatingValue">Valor:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-1">
                                            <div class="form-floating">
                                                <select name="type" class="form-select" id="floatingType" required>
                                                    <option selected="" value="">Tipo:</option>
                                                    <option value="CPF">CPF</option>
                                                    <option value="CNPJ">CNPJ</option>
                                                    <option value="EMAIL">EMAIL</option>
                                                    <option value="PHONE">Telefone:</option>
                                                </select>
                                                <label for="floatingType">Tipo da chave</label>
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
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-success">Solicitar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card p-5">
                    <div class="card-body">
                        <h5 class="card-title">Extrato</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabela" width="100%" cellspacing="0">
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
        </div>
    </section>
@endsection