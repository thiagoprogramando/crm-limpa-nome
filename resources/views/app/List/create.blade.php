@extends('app.layout')
@section('title') Criação de Lista @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Criação de Lista</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Criação de Lista</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Preencha todos os dados da Lista.</h5>
        
                        <form action="{{ route('create-list') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Indique um nome para a Lista:" required>
                                    <label for="floatingName">Indique um nome para a Lista:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control" placeholder="Descrição" id="floatingTextarea" style="height: 100px;"></textarea>
                                    <label for="floatingTextarea">Indique uma descrição para a Lista:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="date" name="date_start" class="form-control" id="floatingDateEnd" placeholder="Indique a data de início:" required>
                                    <label for="floatingDateEnd">Indique a data de início:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="date" name="date_end" class="form-control" id="floatingDateend" placeholder="Indique a data de encerramento:" required>
                                    <label for="floatingDateend">Indique a data de encerramento:</label>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <select name="status" class="form-select" id="floatingStatus">
                                        <option selected value="1">Indique qual status:</option>
                                        <option value="1">Ativar (Quando estiver dentro do relógio)</option>
                                        <option value="2">Inativar</option>
                                    </select>
                                    <label for="floatingStatus">Status</label>
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