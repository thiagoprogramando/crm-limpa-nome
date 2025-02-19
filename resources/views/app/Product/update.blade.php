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

                        <ul class="nav nav-tabs mt-3" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Detalhes</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Contrato</button>
                            </li>
                        </ul>
        
                        <form action="{{ route('update-product') }}" method="POST" class="tab-content pt-2" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row g-3">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $product->id }}">
                                    <div class="col-12 col-md-7 col-lg-7">
                                        <div class="form-floating mb-2">
                                            <input type="text" name="name" value="{{ $product->name }}" class="form-control" id="floatingName" placeholder="Indique um nome para o Produto:" required>
                                            <label for="floatingName">Nome do Produto:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5 col-lg-5">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="address" id="address">
                                            <label class="form-check-label" for="address">Solicitar Endereço ao Cliente</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="terms" id="terms" @if($product->terms) checked @endif>
                                            <label class="form-check-label" for="terms">Aceite de termos</label>
                                        </div>
                                    </div>
                        
                                    <div class="col-12 col-md-7 col-lg-7">
                                        <div class="form-floating mb-2">
                                            <textarea name="description" class="form-control" placeholder="Descrição" id="floatingTextarea" style="height: 100px;">{{ $product->description }}</textarea>
                                            <label for="floatingTextarea">Indique uma descrição para o Produto:</label>
                                        </div>
                                        <div class="form-floating mb-2">
                                            <textarea name="terms_text" class="form-control" placeholder="Indique uma descrição para os Termos" id="floatingTextarea" style="height: 100px;">{{ $product->terms_text }}</textarea>
                                            <label for="floatingTextarea">Indique uma descrição para os Termos:</label>
                                        </div>
                                    </div>
                        
                                    <div class="col-12 col-md-5 col-lg-5 row">
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-floating mb-2">
                                                <input type="text" name="value_cost" value="{{ $product->value_cost }}" class="form-control" id="floatingCost" placeholder="Custo:" oninput="mascaraReal(this)">
                                                <label for="floatingCost">Custo:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-floating mb-2">
                                                <input type="text" name="value_rate" value="{{ $product->value_rate }}" class="form-control" id="floatingRate" placeholder="Taxas:" oninput="mascaraReal(this)">
                                                <label for="floatingRate">Taxas:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-floating mb-2">
                                                <input type="text" name="value_min" value="{{ $product->value_min }}" class="form-control" id="floatingMin" placeholder="Mín de venda:" oninput="mascaraReal(this)">
                                                <label for="floatingMin">Mín de venda:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-floating mb-2">
                                                <input type="text" name="value_max" value="{{ $product->value_max }}" class="form-control" id="floatingMax" placeholder="Máx de venda:" oninput="mascaraReal(this)">
                                                <label for="floatingMax">Máx de venda:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-floating mb-2">
                                                <select name="level" class="form-select" id="floatingLevel">
                                                    <option selected value="{{ $product->level }}">Nível com acesso:</option>
                                                    <option value="1" @selected($product->level == 1)>INICIANTE</option>
                                                    <option value="2" @selected($product->level == 2)>CONSULTOR</option>
                                                    <option value="3" @selected($product->level == 3)>CONSULTOR LÍDER</option>
                                                    <option value="4" @selected($product->level == 4)>REGIONAL</option>
                                                    <option value="5" @selected($product->level == 5)>GERENTE REGIONAL</option>
                                                    <option value="6" @selected($product->level == 6)>VENDEDOR INTERNO</option>
                                                    <option value="7" @selected($product->level == 7)>DIRETOR</option>
                                                    <option value="8" @selected($product->level == 8)>DIRETOR REGIONAL</option>
                                                    <option value="9" @selected($product->level == 9)>PRESIDENTE VIP</option>
                                                    <option value="">TODOS</option>
                                                </select>
                                                <label for="floatingLevel">Opções</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6">
                                            <div class="form-floating mb-2">
                                                <select name="active" class="form-select" id="floatingActive">
                                                    <option value="{{ $product->active }}">Situação:</option>
                                                    <option value="1" @selected($product->active == 1)>Ativo</option>
                                                    <option value="2" @selected($product->active == 2)>Inativo</option>
                                                </select>
                                                <label for="floatingActive">Opções</label>
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div class="col-12 col-md-4 col-lg-4 offset-md-8 offset-lg-8 mb-1 d-grid gap-2 mt-3">
                                        <button type="button" onclick="openTab('#profile-tab')" class="btn btn-primary">Avançar</button>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                    <div class="form-floating mb-2">
                                        <input type="text" name="contract" class="form-control" id="floatingContract" placeholder="Token ZapSing:" value="{{ $product->contract }}">
                                        <label for="floatingContract">Token ZapSing:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                    <textarea name="contract_subject" class="tinymce-editor" placeholder="Contrato (Pré-visualização)" id="question">{{ $product->contract_subject }}</textarea>
                                </div>
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 offset-md-8 offset-lg-8 mb-1 d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('assets/dashboard/vendor/tinymce/tinymce.min.js') }}"></script>
    <script>
        function openTab(tab) {
            $(tab).click();
        }
    </script>
@endsection