@extends('app.layout')
@section('title') Marketing @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Marketing</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Marketing</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    @if(Auth::user()->type == 1) <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#photoshopModal">Criar Mídia</button> @endif
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>

                <div class="modal fade" id="photoshopModal" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('create-photoshop') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Escolha o arquivo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <input class="form-control" type="text" name="name" placeholder="Nome:">
                                        </div>
                                        <div class="col-12 mb-2">
                                            <input class="form-control" type="file" name="file">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-success">Enviar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card p-5">
                    <div class="card-body">
                        <h5 class="card-title">Mídias</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th class="text-center" scope="col">Arq. Original</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($photoshops as $photoshop)
                                        <tr>
                                            <th scope="row">{{ $photoshop->id }}</th>
                                            <td>{{ $photoshop->name }}</td>
                                            <td class="text-center"><a href="{{ Storage::url($photoshop->file) }}" download>Baixar</a></td>
                                            <td class="text-center">
                                                <form action="{{ route('delete-photoshop') }}" method="POST" class="delete">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $photoshop->id }}"> 
                                                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createModal{{ $photoshop->id }}"><i class="bi bi-folder-symlink-fill"></i> Gerar com meus dados</button>
                                                        @if(Auth::user()->type == 1) <button type="submit" class="btn btn-outline-primary"><i class="bi bi-trash"></i></button> @endif
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="createModal{{ $photoshop->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form action="{{ route('create-midia') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="photoshop_id" value="{{ $photoshop->id }}">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Preencha com os dados necessários</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-12 mb-2 text-center">
                                                                    <img id="imagePreview{{ $photoshop->id }}" src="{{ Storage::url($photoshop->file) }}" alt="Imagem Preview" class="img-fluid w-50 mb-2">
                                                                </div>
                                                                <div class="col-12 mb-2">
                                                                    <input class="form-control" type="text" name="name" placeholder="Nome:" value="{{ Auth::user()->name }}">
                                                                </div>
                                                                <div class="col-12 mb-2">
                                                                    <input class="form-control" type="text" name="phone" placeholder="Telefone:" value="{{ Auth::user()->phone }}">
                                                                </div>
                                                                <div class="col-12 mb-2">
                                                                    <input class="form-control" type="hidden" name="photoshop_id" value="{{ $photoshop->id }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                                            <button type="submit" class="btn btn-success">Gerar Mídia</button>
                                                        </div>
                                                    </div>
                                                </form>
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