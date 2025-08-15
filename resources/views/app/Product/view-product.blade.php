@extends('app.layout')
@section('title') Produto: {{ $product->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Produto: {{ $product->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Produto: {{ $product->name }}</li>
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
        
                        <form action="{{ route('updated-product') }}" method="POST" class="tab-content pt-2" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row g-3">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $product->id }}">
                                    <div class="col-12 col-md-8 col-lg-8">
                                        <div class="form-floating mb-2">
                                            <input type="text" name="name" value="{{ $product->name }}" class="form-control" id="name" placeholder="Título:">
                                            <label for="name">Título:</label>
                                        </div>
                                        <div class="form-floating mb-2">
                                            <textarea name="description" class="form-control" placeholder="Descrição" id="description" style="height: 100px;">{{ $product->description }}</textarea>
                                            <label for="description">Descrição:</label>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 col-md-4 col-lg-4">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_cost" value="{{ $product->value_cost }}" class="form-control" id="floatingCost" placeholder="Custo:" oninput="mascaraReal(this)">
                                                    <label for="floatingCost">Custo:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4 col-lg-4">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_rate" value="{{ $product->value_rate }}" class="form-control" id="floatingRate" placeholder="Taxas:" oninput="mascaraReal(this)">
                                                    <label for="floatingRate">Taxas:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4 col-lg-4">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_min" value="{{ $product->value_min }}" class="form-control" id="floatingMin" placeholder="Mín de venda:" oninput="mascaraReal(this)">
                                                    <label for="floatingMin">Mín de venda:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4 col-lg-4">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_max" value="{{ $product->value_max }}" class="form-control" id="floatingMax" placeholder="Máx de venda:" oninput="mascaraReal(this)">
                                                    <label for="floatingMax">Máx de venda:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4 col-lg-4">
                                                <div class="form-floating mb-2">
                                                    <select name="level" class="form-select" id="level">
                                                        <option value="" selected>Todos os níveis</option>
                                                        <option value="1" @selected($product->level == 1)>Iniciante</option>
                                                        <option value="2" @selected($product->level == 2)>Agente Profissional</option>
                                                        <option value="3" @selected($product->level == 3)>Consultor Avançado</option>
                                                        <option value="4" @selected($product->level == 4)>Especialista Executivo</option>
                                                        <option value="5" @selected($product->level == 5)>Gestor Regional</option>
                                                        <option value="6" @selected($product->level == 6)>Diretor Nacional</option>
                                                        <option value="7" @selected($product->level == 7)>Embaixador Master Brasil</option>
                                                    </select>
                                                    <label for="level">Nível com acesso:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4 col-lg-4">
                                                <div class="form-floating mb-2">
                                                    <select name="status" class="form-select" id="status">
                                                        <option value="{{ $product->status }}">Situação:</option>
                                                        <option value="1" @selected($product->status == 1)>Ativo</option>
                                                        <option value="2" @selected($product->status == 2)>Inativo</option>
                                                    </select>
                                                    <label for="status">Opções</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_address" id="request_address" @checked($product->request_address)>
                                            <label class="form-check-label" for="request_address">Endereço</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_selfie" id="request_selfie" @checked($product->request_selfie)>
                                            <label class="form-check-label" for="request_selfie">Selfie</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_contact" id="request_contact" @checked($product->request_contact)>
                                            <label class="form-check-label" for="request_contact">Contatos</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_serasa" id="request_serasa" @checked($product->request_serasa)>
                                            <label class="form-check-label" for="request_serasa">Login Serasa</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_spc" id="request_spc" @checked($product->request_spc)>
                                            <label class="form-check-label" for="request_spc">Login SPC</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_boa_vista" id="request_boa_vista" @checked($product->request_boa_vista)>
                                            <label class="form-check-label" for="request_boa_vista">Login Boa Vista</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_no_document" id="request_no_document" @checked($product->request_no_document)>
                                            <label class="form-check-label" for="request_no_document">Envio Direto (Sem pedir documentação)</label>
                                        </div>

                                        <button type="button" onclick="openTab('#profile-tab')" class="btn btn-outline-primary mt-5 w-100">Avançar</button>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="request_contract" id="request_contract" @checked($product->request_contract)>
                                        <label class="form-check-label" for="request_contract">Obrigatório Assinar Contrato</label>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                    <textarea name="contract_subject" class="tinymce-editor" placeholder="Contrato" id="question">{{ $product->contract_subject }}</textarea>
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

    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script>
        function openTab(tab) {
            $(tab).click();
        }
    </script>
@endsection