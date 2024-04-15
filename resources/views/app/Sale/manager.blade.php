@extends('app.layout')
@section('title') Gestão de Vendas @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Vendas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Vendas</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                <button type="button" class="btn btn-outline-primary">Excel</button>
            </div>

            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('manager-sale') }}" method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                            <label for="floatingCreated_at">Data:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Informe o valor:" oninput="mascaraReal(this)">
                                            <label for="floatingValue">Valor:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <select name="id_list" class="form-select" id="floatingSelect">
                                                <option selected="" value="">Lista:</option>
                                                @foreach ($lists as $list)
                                                    <option value="{{ $list->id }}">{{ $list->name }}</option>  
                                                @endforeach
                                            </select>
                                            <label for="floatingSelect">Listas</label>
                                        </div>
                                    </div>
                                    @if (Auth::user()->type == 1)
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <select name="id_seller" class="form-select" id="floatingSeller">
                                                    <option selected="" value="">Vendedor:</option>
                                                    @foreach ($sellers as $seller)
                                                        <option value="{{ $seller->id }}">{{ $seller->name }}</option>  
                                                    @endforeach
                                                </select>
                                                <label for="floatingSeller">Vendedor</label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-success">Consultar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="card-body">
                    <h5 class="card-title">Vendas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">N°</th>
                                    <th scope="col">Produto</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Vendedor</th>
                                    <th class="text-center" scope="col">V. Venda</th>
                                    <th class="text-center" scope="col">V. Comissão</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    <tr>
                                        <th scope="row">{{ $sale->id }}</th>
                                        <td>{{ $sale->product->name }}</td>
                                        <td>{{ $sale->user->name }}</td>
                                        <td>{{ $sale->seller->name }}</td>
                                        <td class="text-center">R$ {{ number_format($sale->value, 2, ',', '.') }}</td>
                                        <td class="text-center">R$ {{ number_format($sale->commission, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $sale->statusLabel() }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-sale') }}" method="POST" class="delete">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $sale->id }}">
                                                <a href="{{ route('send-contract', ['id' => $sale->id]) }}" class="btn btn-dark text-light"><i class="bi bi-folder-symlink-fill"></i></a>
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
        </div>
    </div>
</section>

@endsection