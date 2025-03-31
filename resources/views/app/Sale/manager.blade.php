@extends('app.layout')
@section('title') Gestão de Vendas @endsection
@section('conteudo')

<style>
    tr.selected {
        background-color: #d1e7dd;
    }

    input[type="checkbox"]:checked + tr {
        background-color: #d1e7dd;
    }
</style>

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
                <button type="button" id="toggle-select" class="btn btn-outline-primary">Selecionar</button>
                <a href="{{ route('manager-sale', array_merge(request()->query(), ['type' => 'excel'])) }}" class="btn btn-outline-primary">Excel</a>
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
                                            <input type="number" name="id" class="form-control" id="floatingId" placeholder="ID:">
                                            <label for="floatingId">ID</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <select name="id_list" class="form-select" id="floatinglist">
                                                <option selected="" value="">Listas:</option>
                                                @foreach ($lists as $list)
                                                    <option value="{{ $list->id }}">{{ $list->name }}</option>  
                                                @endforeach
                                            </select>
                                            <label for="floatinglist">Listas</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <select name="status" class="form-select" id="floatingStatus">
                                                <option selected value="">Opções:</option>
                                                <option value="0">Pendente</option>
                                                <option value="1">Pagamento confirmado</option>
                                            </select>
                                            <label for="floatingStatus">Status</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <select name="label" class="form-select" id="floatingLabel">
                                                <option selected value="">Opções:</option>
                                                <option value="REPROTOCOLADO">Reprotocolado</option>
                                            </select>
                                            <label for="floatingLabel">Label</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <select name="id_seller" class="form-select" id="floatingSeller">
                                                <option selected="" value="">Consultores:</option>
                                                @foreach ($sellers as $seller)
                                                    <option value="{{ $seller->id }}">{{ $seller->name }}</option>  
                                                @endforeach
                                            </select>
                                            <label for="floatingSeller">Consultor</label>
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

                    <div id="action-buttons" class="d-none btn-group mb-2 mt-2">
                        @if(Auth::user()->type == 1)
                            <button id="aproved-all" class="btn btn-primary">Aprovar Todos</button>
                        @endif
                        <button id="create-payment" class="btn btn-outline-primary">Gerar Pagamento</button>
                        <button id="quanty-name" class="btn btn-outline-primary">Nomes: </button>
                    </div>

                    <h5 class="card-title">Vendas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">#ID</th>
                                    <th scope="col">Lista</th>
                                    <th scope="col">Produto</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Consultor</th>
                                    <th class="text-center" scope="col">Contrato</th>
                                    <th class="text-center" scope="col">Pagamento</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    <tr>
                                        <th scope="row">
                                             <input type="checkbox" class="row-checkbox" value="{{ $sale->id }}"> {{ $sale->id }}
                                        </th>
                                        <th>
                                            {{ $sale->list->name }} <br>
                                            <span class="badge bg-{{ $sale->protocolLabel()['color'] }}" title=" {{ $sale->protocolLabel()['title'] }}">
                                                {{ $sale->protocolLabel()['label'] }}
                                            </span>                                            
                                        </th>
                                        <td title="{{ $sale->product->name }}">
                                            {{ implode(' ', array_slice(explode(' ', $sale->product->name), 0, 2)) }} <br>
                                            @isset($sale->guarantee)
                                                <span class="badge bg-primary">
                                                    Garantia: {{ \Carbon\Carbon::parse($sale->guarantee)->format('d/m/Y') }}
                                                </span>
                                            @endisset
                                            <span class="badge bg-success">Valor Venda: R$ {{ number_format($sale->value_total, 2, ',', '.') }}</span>
                                        </td>
                                        <td title="{{ $sale->user->name }}">
                                            {{ implode(' ', array_slice(explode(' ', $sale->user->name), 0, 2)) }} <br>
                                            <span class="badge bg-dark">CPF/CNPJ: {{ $sale->user->cpfcnpjLabel() }}</span>
                                            @isset($sale->label) 
                                                <span class="badge bg-primary">
                                                    {{ $sale->label }}
                                                </span> 
                                            @endisset
                                        </td>
                                        <td title="{{ $sale->seller->parent->name ?? '---' }}">
                                            {{ implode(' ', array_slice(explode(' ', $sale->seller->name), 0, 2)) }} <br>
                                            <span class="badge bg-success">Comissão: R$ {{ number_format($sale->commission, 2, ',', '.') }}</span>
                                            @if ($sale->seller->filiate == Auth::user()->id)
                                                <span class="badge bg-success">Comissão Patrocinador: R$ {{ number_format($sale->commission_filiate, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($sale->payment !== 'ENVIO MANUAL')
                                                @if ($sale->status_contract == 3)
                                                    <a title="Gerar Contrato" href="{{ route('send-contract', ['id' => $sale->id]) }}" class="btn btn-outline-primary"><i class="ri-file-edit-line"></i> Gerar Contrato</a>
                                                @else
                                                    {{ $sale->statusContractLabel() }} <br>
                                                    @isset($sale->url_contract)
                                                        <span class="badge bg-primary">
                                                            <a title="Contrato" href="{{ env('APP_URL').'preview-contract/'.$sale->id }}" target="_blank" class="text-white">Acessar</a>
                                                        </span>
                                                        @if ($sale->status_contract == 2)
                                                            <span class="badge bg-warning">
                                                                <a title="Reenviar Contrato" href="{{ route('send-contract', ['id' => $sale->id]) }}" class="text-white"><i class="ri-file-edit-line"></i> Reenviar Contrato</a>
                                                            </span>
                                                        @endif
                                                    @endisset
                                                @endif
                                            @else
                                                <span class="badge bg-danger">Não disponível para vendas com Envio Direto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $sale->statusLabel() }} <br>
                                            <span class="badge bg-primary">{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</span>
                                            @if ($sale->type == 3)
                                                <span class="badge bg-dark">CUPOM</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-sale') }}" method="POST" class="delete">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $sale->id }}"> 
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                    <a title="Faturas" href="{{ route('update-sale', ['id' => $sale->id]) }}" class="btn btn-outline-primary"><i class="bi bi-currency-dollar"></i></a>
                                                    @if (in_array(\Carbon\Carbon::parse($sale->created_at)->format('m'), ['10', '11', '12']))
                                                        <a title="Reprotocolar" href="{{ route('reprotocol-sale', ['id' => $sale->id]) }}" class="btn btn-outline-primary"><i class="bx bx-check-shield"></i></a>
                                                    @endif
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
    document.addEventListener('DOMContentLoaded', () => {

        const toggleSelectBtn   = document.getElementById('toggle-select');
        const rowCheckboxes     = document.querySelectorAll('.row-checkbox');
        const actionButtons     = document.getElementById('action-buttons');
        const aprovedAll        = document.getElementById('aproved-all');
        const btnCreatePayment  = document.getElementById('create-payment');
        const btnQuantyNames    = document.getElementById('quanty-name');

        let isSelecting = false;

        toggleSelectBtn.addEventListener('click', () => {
            isSelecting = !isSelecting;

            rowCheckboxes.forEach(checkbox => checkbox.checked = isSelecting);
            toggleSelectBtn.textContent = isSelecting ? 'Cancelar' : 'Selecionar';
            updateActionButtons();
        });

        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateActionButtons);
        });

        function updateActionButtons() {
            const selectedIds = getSelectedIds();
            btnQuantyNames.textContent = 'Nomes: ' + selectedIds.length;
            if (selectedIds.length > 0) {
                actionButtons.classList.remove('d-none');
            } else {
                actionButtons.classList.add('d-none');
            }
        }

        function getSelectedIds() {
            return Array.from(rowCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);
        }

        if (aprovedAll) {
            aprovedAll.addEventListener('click', () => {
                const selectedIds = getSelectedIds();
                sendToApi('api/approved-all', selectedIds);
            });
        }

        if (btnCreatePayment) {
            btnCreatePayment.addEventListener('click', () => {
                const selectedIds = getSelectedIds();
                sendToApi('api/create-payment', selectedIds);
            });
        }

        function sendToApi(route, ids) {

            if (ids.length === 0) {
                Swal.fire({
                    title: 'Atenção!',
                    text: 'Nenhuma venda selecionada!',
                    icon: 'info',
                    timer: 2000
                });
                return;
            }

            var userCustomer = @json(Auth::user()->customer);

            fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    ids, 
                    customer: userCustomer  
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Processo concluído! Você será redirecionado para página de Pagamento.',
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Ver Fatura',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (data.invoiceUrl) {
                                window.open(data.invoiceUrl, '_blank');
                                return;
                            }
                            
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Atenção!',
                        text: data.message,
                        icon: 'info',
                        timer: 2000
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Atenção!',
                    text: error,
                    icon: 'info',
                    timer: 2000
                });
            });
        }
    });
</script>

@endsection