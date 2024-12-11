@extends('app.layout')
@section('title') Gestão de Clientes @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Gestão de Clientes</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Gestão de Clientes</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>

                <div class="modal fade" id="filterModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('list-client') }}" method="GET">
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
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                                <label for="floatingCreated_at">Data de cadastro:</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-success">Consultar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card p-2">
                    <div class="card-body">
                        <h5 class="card-title">Meus Clientes</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">N°</th>
                                        <th scope="col">Nome</th>
                                        <th class="text-center" scope="col">Dados</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <th scope="row">{{ $user->id }}</th>
                                            <td>{{ $user->name }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $user->phone }}</span> / <span class="badge bg-primary">{{ $user->email }}</span> <br>
                                                <span class="badge bg-dark">{{ $user->cpfcnpj }}</span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('delete-user') }}" method="POST" class="delete btn-group">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $user->id }}">
                                                    <button type="button" class="btn btn-warning text-light" data-bs-toggle="modal" data-bs-target="#updateModal{{ $user->id }}"><i class="bi bi-arrow-up-right-circle"></i></button>
                                                    <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="updateModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('update-user') }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar dados</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">

                                                                <input type="hidden" name="id" value="{{ $user->id }}">
                                                                
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:" value="{{ $user->name }}">
                                                                        <label for="floatingName">Nome:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email:" value="{{ $user->email }}">
                                                                        <label for="floatingEmail">Email:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCnpj" placeholder="CPF/CNPJ:" oninput="mascaraCpfCnpj(this)" value="{{ $user->cpfcnpj }}">
                                                                        <label for="floatingCpfCnpj">CPF/CNPJ:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="date" name="birth_date" class="form-control" id="floatingBirthDate" placeholder="Data Nascimento:" value="{{ $user->birth_date }}">
                                                                        <label for="floatingBirthDate">Data Nascimento:</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer btn-group">
                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                                            <button type="submit" class="btn btn-success">Atualizar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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