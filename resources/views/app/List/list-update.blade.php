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

                            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <select name="status_protocol" class="form-select" id="status_protocol">
                                                <option selected value="{{ $list->status_protocol }}">Status (Protocolo):</option>
                                                <option value="1" @selected($list->status_protocol == 1)>Regularizado</option>
                                                <option value="2" @selected($list->status_protocol == 2)>Protocolado</option>
                                                <option value="3" @selected($list->status_protocol == 3)>Em Processamento</option>
                                                <option value="4" @selected($list->status_protocol == 4)>Em Fase de Finalização</option>
                                                <option value="0" @selected($list->status_protocol == 0)>Período de Captação</option>
                                            </select>
                                            <label for="status_protocol">Status (Protocolo)</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <select name="status" class="form-select" id="status">
                                                <option selected value="{{ $list->status }}">Status (Lista):</option>
                                                <option value="1" @selected($list->status == 1)>Ativa</option>
                                                <option value="2" @selected($list->status == 2)>Inativa</option>
                                            </select>
                                            <label for="status">Status (Lista)</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating mb-2">
                                            <input type="datetime-local" name="date_start" class="form-control" id="floatingDateStart" placeholder="Data de início:" value="{{ $list->start }}">
                                            <label for="floatingDateStart">Data de início:</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating mb-2">
                                            <input type="datetime-local" name="date_end" class="form-control" id="floatingDateend" placeholder="Data de encerramento:" value="{{ $list->end }}">
                                            <label for="floatingDateend">Data de encerramento:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 offset-md-6 col-md-6 offset-lg-6 col-lg-6 mb-2 d-grid">
                                        <button type="submit" class="btn btn-primary">Salvar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection