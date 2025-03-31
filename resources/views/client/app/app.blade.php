@extends('client.app.layout')
@section('title') Minhas Compras @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Minhas Compras</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app-cliente') }}">Início</a></li>
                <li class="breadcrumb-item active">Minhas Compras</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12 co-sm-12 col-md-12 col-lg-12 m-0 p-0">
                <div class="card">
                    <div class="card-body m-0 p-0">
                        <div class="row">
                            @foreach ($sales as $sale)
                                <div class="col-sm-12 col-md-6 col-lg-6">
                                    <div class="card">
                                        <div class="row g-0">
                                            <div class="col-12 col-sm-12 col-md-5 col-lg-5">
                                                <div class="card-body">
                                                    <h5 class="card-title">{{$sale->product->name }}</h5>
                                                    <p class="card-text">
                                                        {{$sale->product->description }} <br>
                                                        <span class="badge bg-dark">{{ $sale->statusLabel() }} {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-7 col-lg-7">
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $sale->list->name }}</h5>
                                                    <p class="card-text">
                                                        {{$sale->list->description }}
                                                        <span class="badge bg-primary">{{ \Carbon\Carbon::parse($sale->list->end)->format('d/m/Y') }}</span>
                                                    </p>
                                                    @if ($sale->label) 
                                                        <span class="badge bg-primary">{{ $sale->label }}</span> 
                                                    @endif
                                                    <div class="btn-group" role="group">
                                                        <a title="Acessar Contrato" href="{{ $sale->url_contract }}" target="_blank" class="btn btn-outline-primary card-link"><i class="bi bi-file-earmark-text"></i>Contrato</a>
                                                        <a title="Acessar Faturas" href="{{ route('invoice-cliente', ['sale' => $sale->id]) }}" class="btn btn-outline-primary card-link"><i class="bi bi-currency-dollar"></i>Faturas</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <th class="text-center">Serasa</th>
                                                            <th class="text-center">SPC</th>
                                                            <th class="text-center">B. Vista</th>
                                                            <th class="text-center">QUOD</th>
                                                            <th class="text-center">CENPROT</th>
                                                        </thead>
                                                        <tbody>
                                                            <td class="text-center">{{ $sale->list->serasa_status }}</td>
                                                            <td class="text-center">{{ $sale->list->status_spc }}</td>
                                                            <td class="text-center">{{ $sale->list->status_boa_vista }}</td>
                                                            <td class="text-center">{{ $sale->list->status_quod }}</td>
                                                            <td class="text-center">{{ $sale->list->status_cenprot }}</td>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="text-center">
                            {{ $sales->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection