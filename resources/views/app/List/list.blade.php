@extends('app.layout')
@section('title') Listagem de Listas @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Listas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Listagem de Listas</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <a href="{{ route('createlist') }}" class="btn btn-outline-primary">Nova Lista</a>
            </div>

            <div class="card p-2">
                <div class="card-body">
                    <h5 class="card-title">Listas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Lista</th>
                                    <th class="text-center" scope="col">Início</th>
                                    <th class="text-center" scope="col">Encerramento</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-center" scope="col">Serasa</th>
                                    <th class="text-center" scope="col">SPC</th>
                                    <th class="text-center" scope="col">Boa Vista</th>
                                    <th class="text-center" scope="col">CENPROT</th>
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
                                        <td class="text-center">{{ $list->serasa_status }}</td>
                                        <td class="text-center">{{ $list->status_spc }}</td>
                                        <td class="text-center">{{ $list->status_boa_vista }}</td>
                                        <td class="text-center">{{ $list->status_cenprot }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-list') }}" method="POST" class="delete btn-group">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $list->id }}">
                                                @if (Auth::user()->type == 1)
                                                    <a href="{{ route('updatelist', ['id' => $list->id]) }}" class="btn btn-warning text-light"><i class="bi bi-pencil-square"></i></a>
                                                    <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                                @endif
                                                <a href="{{ route('excel-list', ['id' => $list->id]) }}" class="btn btn-success text-light"><i class="bi bi-file-earmark-excel"></i></a>
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