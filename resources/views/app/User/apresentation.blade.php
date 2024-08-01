@extends('app.layout')
@section('title') Apresentação @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Apresentação</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Apresentação</li>
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
                            <form action="{{ route('create-apresentation') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Cadastro de Vídeo/PDF</h5>
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
                                                <input type="file" name="file" class="form-control" id="floatingFile" placeholder="Arquivo:" accept=".pdf, .mp4">
                                                <label for="floatingFile">Arquivo:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <select name="level" class="form-select" id="floatingSelect">
                                                    <option selected="">Indique qual nível terá acesso:</option>
                                                    <option value="1">INICIANTE</option>
                                                    <option value="2">CONSULTOR</option>
                                                    <option value="3">CONSULTOR LÍDER</option>
                                                    <option value="4">REGIONAL</option>
                                                    <option value="5">GERENTE REGIONAL</option>
                                                    <option value="6">VENDEDOR INTERNO</option>
                                                    <option value="">Todos</option>
                                                </select>
                                                <label for="floatingSelect">Níveis</label>
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
                        <h5 class="card-title">Material</h5>
                        
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
                                                <form action="{{ route('delete-apresentation') }}" method="POST" class="delete">
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