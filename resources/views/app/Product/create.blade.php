@extends('app.layout')
@section('title') Criação de Produtos & Negócios @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Criação de Produtos & Negócios</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Criação de Produtos & Negócios</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Preencha todos os dados do Produto.</h5>
        
                        <form action="{{ route('create-product') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-12 col-md-8 col-lg-8 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Indique um nome para o Produto:" required>
                                    <label for="floatingName">Indique um nome para o Produto:</label>
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
                                    <label for="floatingTextarea">Indique uma descrição para o Produto:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_cost" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
                                    <label for="floatingContract">Indique o custo do Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_rate" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
                                    <label for="floatingContract">Indique o custo de taxas do Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <select name="level" class="form-select" id="floatingSelect">
                                        <option selected="">Indique qual nível terá acesso:</option>
                                        <option value="1">Start</option>
                                        <option value="2">Consultor</option>
                                        <option value="3">Consultor líder</option>
                                        <option value="4">Líder</option>
                                        <option value="5">Regional</option>
                                        <option value="6">Vendedor Interno</option>
                                        <option value="">Todos</option>
                                    </select>
                                    <label for="floatingSelect">Níveis</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="contract" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:">
                                    <label for="floatingContract">Indique um ID de contrato para o Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_min" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
                                    <label for="floatingContract">Indique o valor mín de venda para o Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_max" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
                                    <label for="floatingContract">Indique o valor máx de venda para o Produto:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 offset-md-8 offset-lg-8 mb-1 d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-outline-success rounded-pill" type="button">Salvar</button>
                              </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection