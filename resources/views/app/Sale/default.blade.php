@extends('app.layout')
@section('title') Gestão de Inadimplência @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Inadimplências</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Inadimplência</li>
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
                        <form action="{{ route('invoice-default') }}" method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o Nome:">
                                            <label for="floatingName">Cliente:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <select name="id_list" class="form-select" id="floatinglist">
                                                <option selected="" value="">Lista:</option>
                                                @foreach ($lists as $list)
                                                    <option value="{{ $list->id }}">{{ $list->name }}</option>  
                                                @endforeach
                                            </select>
                                            <label for="floatinglist">Listas</label>
                                        </div>
                                    </div>
                                    @if (Auth::user()->type == 1)
                                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                                            <div class="form-floating">
                                                <select name="id_seller" class="form-select" id="floatingSeller">
                                                    <option selected="" value="">Vendedor:</option>
                                                    @foreach ($sellers as $seller)
                                                        <option value="{{ $seller->id }}">{{ $seller->name }}</option>  
                                                    @endforeach
                                                </select>
                                                <label for="floatingSeller">Vendedor</label>
                                            </div>
                                        </div>
                                    @endif
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

            <div class="card p-5">
                <div class="card-body">
                    <h5 class="card-title">Faturas com atrasos</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">N° - Cliente</th>
                                    <th scope="col">Descrição</th>
                                    <th scope="col">Vencimento</th>
                                    <th class="text-center" scope="col">V. Parcela</th>
                                    <th class="text-center" scope="col">V. Comissão</th>
                                    <th class="text-center" scope="col">Status</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <th scope="row">{{ $invoice->num }} - {{ $invoice->user->name }}</th>
                                        <td>{{ $invoice->description }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                        <td class="text-center">R$ {{ number_format($invoice->commission, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $invoice->statusLabel() }}</td>
                                        <td class="text-center">
                                            <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-primary text-light"><i class="bi bi-subtract"></i></a>
                                            <a href="{{ route('send-default-whatsapp', ['id' => $invoice->id]) }}" class="btn btn-success text-light confirm"><i class="bi bi-whatsapp"></i></a>
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