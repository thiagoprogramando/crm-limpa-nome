@extends('app.layout')
@section('title') Listas de Vendas @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Listas de Vendas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Listas de Vendas</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createModal">Nova Lista</button>
                <button type="button" class="btn btn-outline-primary" id="gerarExcel">Excel</button>

                <div class="modal fade" id="createModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="{{ route('created-list') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:" required>
                                                <label for="floatingName">Nome:</label>
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
                                                <input type="datetime-local" name="date_start" class="form-control" id="floatingDateEnd" placeholder="Data de início:" required>
                                                <label for="floatingDateEnd">Data de início:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                                            <div class="form-floating">
                                                <input type="datetime-local" name="date_end" class="form-control" id="floatingDateend" placeholder="Data de encerramento:" required>
                                                <label for="floatingDateend">Data de encerramento:</label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 col-md-4 col-lg-4 mb-1">
                                            <div class="form-floating">
                                                <select name="status" class="form-select" id="floatingStatus">
                                                    <option selected value="1">Status:</option>
                                                    <option value="1">Ativa (Quando estiver dentro do relógio)</option>
                                                    <option value="2">Inativa</option>
                                                </select>
                                                <label for="floatingStatus">Status</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer btn-group">
                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body m-0 p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Lista</th>
                                    <th class="text-center" scope="col">Início</th>
                                    <th class="text-center" scope="col">Encerramento</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lists as $list)
                                    <tr>
                                        <th scope="row">{{ $list->id }}</th>
                                        <td>
                                            {{ $list->name }} <br>
                                            <span class="badge bg-dark"> {{ $list->description }} </span>
                                        </td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($list->start)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($list->end)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">{{ $list->statusLabel() }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('deleted-list') }}" method="POST" class="delete btn-group">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $list->id }}">
                                                @if (Auth::user()->type == 1)
                                                    <a href="{{ route('view-list', ['id' => $list->id]) }}" class="btn btn-warning text-light"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                                @endif
                                                <a href="{{ route('list-excel', ['id' => $list->id]) }}" class="btn btn-success text-light"><i class="bi bi-file-earmark-excel"></i></a>
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