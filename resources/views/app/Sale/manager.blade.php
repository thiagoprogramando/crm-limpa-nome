@extends('app.layout')
@section('title') Gestão de Vendas @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Vendas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Vendas</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                @if(Auth::user()->type == 1)
                    <a href="#" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="bi bi-trash"></i> Não Assinados
                    </a>
                @endif
            </div>

            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('manager-sale') }}" method="GET">
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
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                            <label for="floatingCreated_at">Data:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Informe o valor:" oninput="mascaraReal(this)">
                                            <label for="floatingValue">Valor:</label>
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
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <select name="status" class="form-select" id="floatingStatus">
                                                <option selected value="">Status:</option>
                                                <option value="0">Pendente</option>
                                                <option value="1">Pagamento confirmado</option>
                                                <option value="2">Contrato Assinado</option>
                                                <option value="3">Pendente de Assinatura</option>
                                                <option value="4">Pendente de Pagamento</option>
                                            </select>
                                            <label for="floatingStatus">Status</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <select name="label" class="form-select" id="floatingLabel">
                                                <option selected value="">Label:</option>
                                                <option value="REPROTOCOLADO">Reprotocolado</option>
                                            </select>
                                            <label for="floatingLabel">Label</label>
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
                    <h5 class="card-title">Vendas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">N°</th>
                                    <th scope="col">Lista</th>
                                    <th scope="col">Produto</th>
                                    <th scope="col">Cliente</th>
                                    <th class="d-none">Telefone</th>
                                    <th>CPF/CNPJ</th>
                                    <th scope="col">Vendedor</th>
                                    <th class="text-center" scope="col">V. Venda</th>
                                    <th class="text-center" scope="col">V. Comissão</th>
                                    <th class="text-center" scope="col">Status - Data</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    <tr>
                                        <th scope="row">
                                            {{ $sale->id }} <br> 
                                            @if($sale->label) <span class="badge bg-primary">{{ $sale->label }} - {{ \Carbon\Carbon::parse($sale->update_at)->format('d/m/Y') }}</span> @endif
                                        </th>
                                        <th>
                                            {{ $sale->list->name }} <br>
                                            <span class="badge bg-dark">Serasa {{ $sale->list->serasa_status }}</span>
                                            <span class="badge bg-dark">SPC {{ $sale->list->status_spc }}</span>
                                            <span class="badge bg-dark">Boa Vista {{ $sale->list->status_boa_vista }}</span>
                                            <span class="badge bg-dark">QUOD {{ $sale->list->status_quod }}</span>
                                            <span class="badge bg-dark">CENPROT {{ $sale->list->status_cenprot }}</span>
                                        </th>
                                        <td title="{{ $sale->product->name }}">
                                            {{ substr($sale->product->name, 0, 15) }} <br>
                                            <span class="badge bg-primary">Garantia: @if($sale->guarantee) {{ \Carbon\Carbon::parse($sale->guarantee)->format('d/m/Y') }} @else --- @endif</span>
                                        </td>
                                        <td>{{ $sale->user->name }}</td>
                                        <td class="d-none">{{ $sale->user->phone }}</td>
                                        <td>{{ $sale->user->cpfcnpjLabel() }}</td>
                                        <td title="{{ $sale->seller->indicator() }}">{{ substr($sale->seller->name, 0, 15) }}..</td>
                                        <td class="text-center">R$ {{ number_format($sale->value, 2, ',', '.') }}</td>
                                        <td class="text-center">R$ {{ number_format($sale->commission, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $sale->statusLabel() }} - {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-sale') }}" method="POST" class="delete">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $sale->id }}"> 
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                    <a title="Contrato" href="{{ $sale->url_contract }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-file-earmark-text"></i></a>
                                                    <a title="Faturas" href="{{ route('update-sale', ['id' => $sale->id]) }}" class="btn btn-outline-primary"><i class="bi bi-currency-dollar"></i></a>
                                                    <a title="Reprotocolar" href="{{ route('reprotocol-sale', ['id' => $sale->id]) }}" class="btn btn-outline-primary"><i class="bx bx-check-shield"></i></a>
                                                    <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function confirmDelete() {
        Swal.fire({
            title: 'Você tem certeza?',
            text: "Essa ação irá remover todas as vendas e faturas pendentes!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, deletar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('delete-sales-pending') }}";
            }
        });
    }
</script>

@endsection