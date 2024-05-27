@extends('app.layout')
@section('title') Listagem de Produtos @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Listagem de Produtos</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card p-5">
                <div class="card-body">

                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#createProduct">Cadastrar</button>
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#disablebackdrop">Filtrar</button>
                    </div>

                    <h5 class="card-title">Produtos</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nome</th>
                                    <th class="text-center" scope="col">Valor Mín</th>
                                    <th class="text-center" scope="col">Valor Máx</th>
                                    <th class="text-center" scope="col">To. Vendas</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <th scope="row">{{ $product->id }}</th>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-center">R$ {{ number_format($product->value_min, 2, ',', '.') }}</td>
                                        <td class="text-center">R$ {{ number_format($product->value_max, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $product->totalSale() }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-product') }}" method="POST" class="delete">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $product->id }}">
                                                <a href="{{ route('detail-product', ['id' => $product->id]) }}" class="btn btn-warning text-light"><i class="bi bi-pencil-square"></i></a>
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

<div class="modal fade" id="createProduct" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cadastro de Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('create-product') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-8 col-lg-8 mb-1">
                            <div class="form-floating">
                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Indique um nome para o Produto:" required>
                                <label for="floatingName">Nome:</label>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="address" id="address">
                                <label class="form-check-label" for="address">Solicitar Endereço ao Cliente</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="createuser" id="user">
                                <label class="form-check-label" for="user">Criar usuário para o Cliente</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                            <div class="form-floating">
                                <textarea name="description" class="form-control" placeholder="Descrição" id="floatingTextarea" style="height: 100px;"></textarea>
                                <label for="floatingTextarea">Descrição:</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                            <div class="form-floating">
                                <input type="text" name="value_cost" class="form-control" id="floatingContract" placeholder="Indique o custo do Produto:" oninput="mascaraReal(this)">
                                <label for="floatingContract">Custo:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                            <div class="form-floating">
                                <input type="text" name="value_rate" class="form-control" id="floatingContract" placeholder="Indique o custo de taxas do Produto:" oninput="mascaraReal(this)">
                                <label for="floatingContract">Taxas:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                            <div class="form-floating">
                                <select name="level" class="form-select" id="floatingSelect">
                                    <option selected value="">Qual nível terá acesso:</option>
                                    <option value="1">Iniciante</option>
                                    <option value="2">Consultor</option>
                                    <option value="3">Líder</option>
                                    <option value="4">Gerente</option>
                                    <option value="6">Vendedor interno</option>
                                    <option value="">Todos</option>
                                </select>
                                <label for="floatingSelect">Níveis</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                            <div class="form-floating">
                                <input type="text" name="contract" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:">
                                <label for="floatingContract">ID do contrato:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                            <div class="form-floating">
                                <input type="text" name="value_min" class="form-control" id="floatingContract" placeholder="Indique o valor mín de venda para o Produto:" oninput="mascaraReal(this)">
                                <label for="floatingContract">Valor mín de venda:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                            <div class="form-floating">
                                <input type="text" name="value_max" class="form-control" id="floatingContract" placeholder="Indique o valor máx de venda para o Produto:" oninput="mascaraReal(this)">
                                <label for="floatingContract">Valor máx de venda:</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-outline-success">Cadastrar e continuar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection