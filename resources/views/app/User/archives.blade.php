@extends('app.layout')
@section('title') Consultas e arquivos @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Consultas e arquivos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Modo Cal Center</a></li>
                <li class="breadcrumb-item active">Consultas e arquivos</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                    @if(Auth::user()->type === 1)
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#createModal">Cadastrar</button>
                    @endif
                </div>

                <div class="modal fade" id="createModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('create-archive') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Cadastro de consultas e arquivos</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <input type="text" name="title" class="form-control" id="floatingTitle" placeholder="Título:">
                                                <label for="floatingTitle">Título:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <input type="file" name="file" class="form-control" id="floatingFile" placeholder="Arquivo:">
                                                <label for="floatingFile">Arquivo:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <select name="id_user" class="form-select" id="floatingUser">
                                                    <option selected="" value="">Usuário:</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>  
                                                    @endforeach
                                                </select>
                                                <label for="floatingUser">Usuário</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-success">Cadastrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card p-5">
                    <div class="card-body">
                        <h5 class="card-title">Consultas e arquivos</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Título</th>
                                        <th class="text-center" scope="col">Acesso</th>
                                        <th class="text-center" scope="col">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($archives as $archive)
                                        <tr>
                                            <th scope="row">{{ $archive->id }}</th>
                                            <td><a href="{{ url("storage/{$archive->file}") }}" target="_blank">{{ $archive->title }}</a></td>
                                            <td class="text-center">  
                                                <form action="{{ route('delete-archive') }}" method="POST" class="delete">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $archive->id }}"> 
                                                    <a href="{{ url("storage/{$archive->file}") }}" target="_blank" class="btn btn-primary text-light"> <i class="bi bi-archive"></i> </a>
                                                    @if(Auth::user()->type == 1) <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button> @endif
                                                </form>
                                            </td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($archive->created_at)->format('d/m/Y') }}</td>
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