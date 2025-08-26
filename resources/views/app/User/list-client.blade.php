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
                                        <div class="col-12 col-md-7 col-lg-7 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:">
                                                <label for="floatingName">Nome:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5 col-lg-5 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                                <label for="floatingCreated_at">Data de cadastro:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-7 col-lg-7 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="email" class="form-control" id="floatingEmail" placeholder="Email:">
                                                <label for="floatingEmail">Email:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5 col-lg-5 mb-2">
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

                <div class="card">
                    <div class="card-body p-0 m-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">N°</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Dados</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <th scope="row">{{ $user->id }}</th>
                                            <td>{{ $user->name }}</td>
                                            <td>
                                                <span class="badge bg-primary">{{ 'Telefone: '.$user->phone }}</span> <span class="badge bg-primary">{{ 'Email: '.$user->email }}</span>
                                                <span class="badge bg-dark">{{ 'CPF/CNPJ: '.$user->cpfcnpjLabel() }}</span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('deleted-user') }}" method="POST" class="delete btn-group">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $user->id }}">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal{{ $user->id }}"><i class="bi bi-arrow-up-right-circle"></i></button>
                                                    @if (Auth::user()->type == 1)
                                                        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-trash"></i></button>
                                                    @endif 
                                                </form>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="updateModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('updated-user') }}" method="POST">
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
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCnpj" placeholder="CPF/CNPJ:" oninput="mascaraCpfCnpj(this)" value="{{ $user->cpfcnpj }}">
                                                                        <label for="floatingCpfCnpj">CPF/CNPJ:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="date" name="birth_date" class="form-control" id="floatingBirthDate" placeholder="Data Nascimento:" value="{{ $user->birth_date }}">
                                                                        <label for="floatingBirthDate">Data Nascimento:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email:" value="{{ $user->email }}">
                                                                        <label for="floatingEmail">Email:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="phone" class="form-control" id="floatingPhone" placeholder="Whatsapp:" oninput="mascaraTelefone(this)" value="{{ $user->phone }}">
                                                                        <label for="floatingPhone">Whatsapp:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <select name="type" class="form-select" id="floatingType">
                                                                            <option selected value="{{ $user->type }}">Tipos:</option>
                                                                            <option value="2" @selected($user->type == 2)>Consultor</option>
                                                                            <option value="3" @selected($user->type == 3)>Cliente</option>
                                                                        </select>
                                                                        <label for="floatingType">Alterar Permissões</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer btn-group">
                                                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                                            <button type="submit" class="btn btn-primary">Atualizar</button>
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
                    <div class="card-footer text-center">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection