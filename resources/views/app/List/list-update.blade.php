@extends('app.layout')
@section('title') Configurações da Lista: {{ $list->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Configurações da Lista: {{ $list->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Configurações da Lista: {{ $list->name }}</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('updated-list') }}" method="POST" class="row mt-3">
                            @csrf
                            <input type="hidden" name="id" value="{{ $list->id }}">
                            <div class="col-12 col-sm-12 col-md-5 col-lg-5">
                                <div class="form-floating mb-2">
                                    <input type="text" name="name" class="form-control" id="name" placeholder="Nome:" value="{{ $list->name }}" required>
                                    <label for="name">Nome:</label>
                                </div>
                                
                                <div class="form-floating mb-2">
                                    <textarea name="description" class="form-control" placeholder="Descrição" id="description" style="height: 100px;">{{ $list->description }}</textarea>
                                    <label for="description">Descrição:</label>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-7 col-lg-7 row">
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating">
                                        <select name="status_serasa" class="form-select" id="floatingStatus">
                                            <option selected value="{{ $list->status_serasa }}">Opções:</option>
                                            <option value="Baixado" @selected($list->status_serasa == 'Baixado')>Baixado</option>
                                            <option value="Em Andamento" @selected($list->status_serasa == 'Em Andamento')>Em Andamento</option>
                                        </select>
                                        <label for="floatingStatus">Status Serasa:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating">
                                        <select name="status_spc" class="form-select" id="floatingSPC">
                                            <option selected value="{{ $list->status_spc }}">Opções:</option>
                                            <option value="Baixado" @selected($list->status_spc == 'Baixado')>Baixado</option>
                                            <option value="Em Andamento" @selected($list->status_spc == 'Em Andamento')>Em Andamento</option>
                                        </select>
                                        <label for="floatingSPC">Status SPC:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating">
                                        <select name="status_boa_vista" class="form-select" id="floatingStatus">
                                            <option selected value="{{ $list->status_boa_vista }}">Opções:</option>
                                            <option value="Baixado" @selected($list->status_boa_vista == 'Baixado')>Baixado</option>
                                            <option value="Em Andamento" @selected($list->status_boa_vista == 'Em Andamento')>Em Andamento</option>
                                        </select>
                                        <label for="floatingStatus">Status Boa Vista:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating">
                                        <select name="status_quod" class="form-select" id="floatingStatus">
                                            <option selected value="{{ $list->status_quod }}">Opções:</option>
                                            <option value="Baixado" @selected($list->status_quod == 'Baixado')>Baixado</option>
                                            <option value="Em Andamento" @selected($list->status_quod == 'Em Andamento')>Em Andamento</option>
                                        </select>
                                        <label for="floatingStatus">Status QUOD:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating">
                                        <select name="status_cenprot" class="form-select" id="floatingStatus">
                                            <option selected value="{{ $list->status_cenprot }}">Opções:</option>
                                            <option value="Baixado" @selected($list->status_cenprot == 'Baixado')>Baixado</option>
                                            <option value="Em Andamento" @selected($list->status_cenprot == 'Em Andamento')>Em Andamento</option>
                                        </select>
                                        <label for="floatingStatus">Status CENPROT:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating">
                                        <select name="status" class="form-select" id="floatingStatus">
                                            <option selected value="{{ $list->status }}">Indique qual status:</option>
                                            <option value="1" @selected($list->status == 1)>Ativa (Quando estiver dentro do relógio)</option>
                                            <option value="2" @selected($list->status == 2)>Inativa</option>
                                        </select>
                                        <label for="floatingStatus">Status Lista</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating mb-2">
                                        <input type="datetime-local" name="date_start" class="form-control" id="floatingDateStart" placeholder="Data de início:" value="{{ $list->start }}">
                                        <label for="floatingDateStart">Data de início:</label>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <div class="form-floating mb-2">
                                        <input type="datetime-local" name="date_end" class="form-control" id="floatingDateend" placeholder="Data de encerramento:" value="{{ $list->end }}">
                                        <label for="floatingDateend">Data de encerramento:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 col-lg-4 mb-1">
                                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection