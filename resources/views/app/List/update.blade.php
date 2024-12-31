@extends('app.layout')
@section('title') Configurações da Lista: {{ $list->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Configurações da Lista: {{ $list->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Configurações da Lista: {{ $list->name }}</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Mantenha os dados atualizados.</h5>
        
                        <form action="{{ route('update-list') }}" method="POST" class="row g-3">
                            @csrf

                            <input type="hidden" name="id" value="{{ $list->id }}">

                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Indique um nome para a Lista:" value="{{ $list->name }}" required>
                                    <label for="floatingName">Lista:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <select name="serasa_status" class="form-select" id="floatingStatus">
                                        <option selected value="{{ $list->serasa_status }}">Opções:</option>
                                        <option value="Baixado" @selected($list->serasa_status == 'Baixado')>Baixado</option>
                                        <option value="Em Andamento" @selected($list->serasa_status == 'Em Andamento')>Em Andamento</option>
                                    </select>
                                    <label for="floatingStatus">Situação Serasa:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <select name="status_spc" class="form-select" id="floatingSPC">
                                        <option selected value="{{ $list->status_spc }}">Opções:</option>
                                        <option value="Baixado" @selected($list->status_spc == 'Baixado')>Baixado</option>
                                        <option value="Em Andamento" @selected($list->status_spc == 'Em Andamento')>Em Andamento</option>
                                    </select>
                                    <label for="floatingSPC">Situação SPC:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <select name="status_boa_vista" class="form-select" id="floatingStatus">
                                        <option selected value="{{ $list->status_boa_vista }}">Opções:</option>
                                        <option value="Baixado" @selected($list->status_boa_vista == 'Baixado')>Baixado</option>
                                        <option value="Em Andamento" @selected($list->status_boa_vista == 'Em Andamento')>Em Andamento</option>
                                    </select>
                                    <label for="floatingStatus">Situação Boa Vista:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <select name="status_quod" class="form-select" id="floatingStatus">
                                        <option selected value="{{ $list->status_quod }}">Opções:</option>
                                        <option value="Baixado" @selected($list->status_quod == 'Baixado')>Baixado</option>
                                        <option value="Em Andamento" @selected($list->status_quod == 'Em Andamento')>Em Andamento</option>
                                    </select>
                                    <label for="floatingStatus">Situação QUOD:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 col-lg-2 mb-1">
                                <div class="form-floating">
                                    <select name="status_cenprot" class="form-select" id="floatingStatus">
                                        <option selected value="{{ $list->status_cenprot }}">Opções:</option>
                                        <option value="Baixado" @selected($list->status_cenprot == 'Baixado')>Baixado</option>
                                        <option value="Em Andamento" @selected($list->status_cenprot == 'Em Andamento')>Em Andamento</option>
                                    </select>
                                    <label for="floatingStatus">Situação CENPROT:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control" placeholder="Descrição" id="floatingTextarea" style="height: 100px;">{{ $list->description }}</textarea>
                                    <label for="floatingTextarea">Indique uma descrição para a Lista:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="datetime-local" name="date_start" class="form-control" id="floatingDateStart" placeholder="Data de início:" value="{{ $list->start }}">
                                    <label for="floatingDateStart">Data de início:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <input type="datetime-local" name="date_end" class="form-control" id="floatingDateend" placeholder="Data de encerramento:" value="{{ $list->end }}">
                                    <label for="floatingDateend">Data de encerramento:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 mb-1">
                                <div class="form-floating">
                                    <select name="status" class="form-select" id="floatingStatus">
                                        <option selected value="{{ $list->status }}">Indique qual status:</option>
                                        <option value="1" @selected($list->status == 1)>Ativa (Quando estiver dentro do relógio)</option>
                                        <option value="2" @selected($list->status == 2)>Inativa</option>
                                    </select>
                                    <label for="floatingStatus">Status</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4 offset-md-8 offset-lg-8 mb-1 d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-outline-success" type="button">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection