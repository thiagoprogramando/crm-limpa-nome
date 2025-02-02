@extends('app.layout')
@section('title') Recuperação - Vendas @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Recuperação - Vendas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Recuperação - Vendas</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
            </div>

            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('trash-sales') }}" method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o Nome:">
                                            <label for="floatingName">Cliente:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                            <label for="floatingCreated_at">Data:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="number" name="id" class="form-control" id="floatingId" placeholder="ID:">
                                            <label for="floatingId">ID</label>
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

            <div class="card p-2">
                <div class="card-body">
                    <h5 class="card-title">Vendas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Consultor</th>
                                    <th class="text-center" scope="col">Contrato</th>
                                    <th class="text-center" scope="col">Pagamento</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    <tr>
                                        <th scope="row"> {{ $sale->id }} </th>
                                        <td title="{{ $sale->user->name }}">
                                            {{ implode(' ', array_slice(explode(' ', $sale->user->name), 0, 2)) }} <br>
                                            <span class="badge bg-dark">CPF/CNPJ: {{ $sale->user->cpfcnpjLabel() }}</span>
                                            @isset($sale->label) 
                                                <span class="badge bg-primary">
                                                    {{ $sale->label }}
                                                </span> 
                                            @endisset
                                        </td>
                                        <td title="{{ $sale->seller->parent->name ?? '---' }}">
                                            {{ implode(' ', array_slice(explode(' ', $sale->seller->name), 0, 2)) }} <br>
                                            <span class="badge bg-success">Comissão: R$ {{ number_format($sale->commission, 2, ',', '.') }}</span>
                                            @if ($sale->seller->filiate == Auth::user()->id)
                                                <span class="badge bg-success">Comissão Patrocinador: R$ {{ number_format($sale->commission_filiate, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($sale->payment !== 'ENVIO MANUAL')
                                                {{ $sale->statusContractLabel() }} <br>
                                                @isset($sale->url_contract)
                                                    <span class="badge bg-primary">
                                                        <a title="Contrato" href="{{ $sale->url_contract }}" target="_blank" class="text-white">Acessar</a>
                                                    </span>
                                                @endisset
                                            @else
                                                <span class="badge bg-danger">Não disponível para vendas com Envio Direto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $sale->statusLabel() }} <br>
                                            <span class="badge bg-primary">{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('sale-recover') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $sale->id }}"> 
                                                <div class="btn-group" role="group">
                                                    <button type="submit" class="btn btn-success text-light"><i class="bi bi-recycle"></i> Recuperar</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection