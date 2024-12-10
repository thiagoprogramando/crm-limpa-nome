@extends('app.layout')
@section('title') Recebíveis @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Recebíveis</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Extrato de Recebíveis</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>
                
                <div class="card p-2">
                    <div class="card-body">
                        <h5 class="card-title">Extrato</h5>
                        
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
                                                <th scope="row">{{ $receivable['id'] }}</th>
                                                <td>{{ $receivable['description'] }}</td>
                                                <td class="text-justify">R$ {{ number_format($receivable['value'], 2, ',', '.') }}</td>
                                                <td class="text-center">{{ \Carbon\Carbon::parse($receivable['date'])->format('d/m/Y') }}</td>
                                            </tr>
                                        @endif
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