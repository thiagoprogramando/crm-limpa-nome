@extends('app.layout')
@section('title') Configurações do Produto: {{ $product->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Configurações do Produto: {{ $product->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Configurações do Produto: {{ $product->name }}</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Altere apenas o que precisar.</h5>
        
                        <form action="{{ route('update-product') }}" method="POST" class="row g-3">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <div class="col-12 col-md-8 col-lg-8 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="name" value="{{ $product->name }}" class="form-control" id="floatingName" placeholder="Indique um nome para o Produto:" required>
                                    <label for="floatingName">Indique um nome para o Produto:</label>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="address" id="address" @if($product->address) checked @endif>
                                    <label class="form-check-label" for="address">Solicitar Endereço ao Cliente</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="createuser" id="user" @if($product->createuser) checked @endif>
                                    <label class="form-check-label" for="user">Criar usuário para o Cliente</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control" placeholder="Descrição" id="floatingTextarea" style="height: 100px;">{{ $product->description }}</textarea>
                                    <label for="floatingTextarea">Indique uma descrição para o Produto:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_cost" value="{{ $product->value_cost }}" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
                                    <label for="floatingContract">Indique o custo do Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_rate" value="{{ $product->value_rate }}" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
                                    <label for="floatingContract">Indique o custo de taxas do Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <select name="level" class="form-select" id="floatingSelect">
                                        <option selected value="{{ $product->level }}">Indique qual nível terá acesso:</option>
                                        <option value="1">Start</option>
                                        <option value="2">Consultor</option>
                                        <option value="3">Líder</option>
                                        <option value="4">Regional</option>
                                        <option value="">Todos</option>
                                    </select>
                                    <label for="floatingSelect">Níveis</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="contract" value="{{ $product->contract }}" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:">
                                    <label for="floatingContract">Indique um ID de contrato para o Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_min" value="{{ $product->value_min }}" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
                                    <label for="floatingContract">Indique o valor mín de venda para o Produto:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_max" value="{{ $product->value_max }}" class="form-control" id="floatingContract" placeholder="Indique um ID de contrato para o Produto:" oninput="mascaraReal(this)">
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

        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Adicione formas de pagamentos.</h5>

                        <form action="{{ route('create-payment') }}" method="POST" class="row g-3">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <select name="method" class="form-select" id="floatingMethod">
                                        <option selected value="PIX">Escolha uma Opção:</option>
                                        <option value="CREDIT_CARD">Cartão de Crédito</option>
                                        <option value="BOLETO">Boleto</option>
                                        <option value="PIX">Pix</option>
                                    </select>
                                    <label for="floatingMethod">Formas de Pagamento</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <input type="number" name="installments" class="form-control" id="floatingInstallments" placeholder="Indique o número de Parcelas:" required>
                                    <label for="floatingInstallments">Parcelas:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value_rate" class="form-control" id="floatingValueRate" placeholder="Indique o valor de Taxas ou acréscimos:" oninput="mascaraReal(this)">
                                    <label for="floatingValueRate">Taxas (Acréscimos):</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 col-lg-2 d-grid gap-2 mb-1">
                                <button type="submit" class="btn btn-outline-success rounded-pill" type="button">Adicionar</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover mt-3">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Método</th>
                                        <th class="text-center" scope="col">Parcelas</th>
                                        <th class="text-center" scope="col">Taxas</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payments as $payment)
                                        <tr>
                                            <th scope="row">{{ $payment->id }}</th>
                                            <td>{{ $payment->methodLabel() }}</td>
                                            <td class="text-center">{{ $payment->installments }}</td>
                                            <td class="text-center">R$ {{ number_format($payment->value_rate, 2, ',', '.') }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('delete-payment') }}" method="POST" class="delete">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $payment->id }}">
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