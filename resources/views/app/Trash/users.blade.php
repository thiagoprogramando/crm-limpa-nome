@extends('app.layout')
@section('title') Recuperação - Usuários/Clientes @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Recuperação - Usuários/Clientes</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Recuperação - Usuários/Clientes</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
            </div>

            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('trash-users') }}" method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:">
                                            <label for="floatingName">Nome:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="email" class="form-control" id="floatingEmail" placeholder="Email:">
                                            <label for="floatingEmail">Email:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCNpj" placeholder="CPF/CNPJ:">
                                            <label for="floatingCpfCNpj">CPF/CNPJ:</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Consultar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card p-2">
                <div class="card-body">
                    <h5 class="card-title">Vendas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Dados</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <th scope="row"> {{ $user->id }} </th>
                                        <td title="{{ $user->name }}">
                                            {{ $user->name }} <br>
                                            <span class="badge bg-primary">CPF/CNPJ: {{ $user->cpfcnpjLabel() }}</span>
                                            <span class="badge bg-dark">Email: {{ $user->email }}</span>
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('user-recover') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $user->id }}"> 
                                                <div class="btn-group" role="group">
                                                    <button type="submit" class="btn btn-success text-light"><i class="bi bi-recycle"></i> Recuperar</button>
                                                </div>
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