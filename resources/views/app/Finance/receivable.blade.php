@extends('app.layout')
@section('title') Recebíveis @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Recebíveis</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Extrato de Recebíveis</li>
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
                            <form action="{{ route('receivable') }}" method="GET">
                                <div class="modal-header">
                                    <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-6 mb-1">
                                            <div class="form-floating">
                                                <input type="date" name="start_date" class="form-control" id="start_date" placeholder="Data inicial:">
                                                <label for="start_date">Data inicial:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-1">
                                            <div class="form-floating">
                                                <input type="date" name="finish_date" class="form-control" id="finish_date" placeholder="Data final:">
                                                <label for="finish_date">Data final:</label>
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
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Descrição</th>
                                        <th class="text-center" scope="col">Valor</th>
                                        <th class="text-center" scope="col">Vencimento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($receivables as $receivable)
                                        @if($receivable['value'] > 0)
                                            <tr>
                                                <th>
                                                    <a href=""><b>{{ $receivable['id'] }}</b></a>
                                                </th>
                                                <td>{{ $receivable['description'] }}</td>
                                                <td class="text-justify">R$ {{ number_format($receivable['value'], 2, ',', '.') }}</td>
                                                <td class="text-center">{{ \Carbon\Carbon::parse($receivable['date'])->format('d/m/Y') }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between mt-3">
                                
                                    <a href="{{ $offset == 0 ? '#' : route('receivable', ['offset' => $offset - 100, 'start_date' => request('start_date'), 'finish_date' => request('finish_date')]) }}" class="btn btn-primary">
                                        Página Anterior
                                    </a>
                               
                            
                                @if ($hasMore)
                                    <a href="{{ route('receivable', ['offset' => $offset + 100, 'start_date' => request('start_date'), 'finish_date' => request('finish_date')]) }}" class="btn btn-primary">
                                        Próxima Página
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection