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
                <button id="toggle-select" class="btn btn-primary">Selecionar</button>
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

                    <div id="action-buttons" class="d-none btn-group mb-3">
                        <button id="gerar-pagamento" class="btn btn-outline-success">Gerar Pagamento</button>
                        <button id="aprovar-todos" class="btn btn-outline-warning">Aprovar Todos</button>
                    </div>

                    <h5 class="card-title">Vendas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col"><input type="checkbox" id="select-all">N°</th>
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
                                            <input type="checkbox" class="row-checkbox" value="{{ $sale->id }}"> {{ $sale->id }} - <br> 
                                        </th>
                                        <th>
                                            {{ $sale->list->name }} <br>
                                            <span class="badge bg-primary">Serasa {{ $sale->list->serasa_status }}</span>
                                            <span class="badge bg-primary">SPC {{ $sale->list->status_spc }}</span>
                                            <span class="badge bg-primary">Boa Vista {{ $sale->list->status_boa_vista }}</span>
                                            <span class="badge bg-primary">QUOD {{ $sale->list->status_quod }}</span>
                                            <span class="badge bg-primary">CENPROT {{ $sale->list->status_cenprot }}</span>
                                        </th>
                                        <td title="{{ $sale->product->name }}">
                                            {{ substr($sale->product->name, 0, 15) }} <br>
                                            <span class="badge bg-primary">Garantia: @if($sale->guarantee) {{ \Carbon\Carbon::parse($sale->guarantee)->format('d/m/Y') }} @else --- @endif</span>
                                        </td>
                                        <td>{{ $sale->user->name }}</td>
                                        <td class="d-none">{{ $sale->user->phone }}</td>
                                        <td>
                                            {{ $sale->user->cpfcnpjLabel() }} <br>
                                            @if($sale->label) <span class="badge bg-primary">{{ $sale->label }} - {{ \Carbon\Carbon::parse($sale->update_at)->format('d/m/Y') }}</span> @endif
                                        </td>
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

    document.addEventListener('DOMContentLoaded', () => {
        const toggleSelectBtn = document.getElementById('toggle-select');
        const selectAllCheckbox = document.getElementById('select-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const actionButtons = document.getElementById('action-buttons');
        const gerarPagamentoBtn = document.getElementById('gerar-pagamento');
        const aprovarTodosBtn = document.getElementById('aprovar-todos');

        let isSelecting = false;

        toggleSelectBtn.addEventListener('click', () => {
            isSelecting = !isSelecting;

            rowCheckboxes.forEach(checkbox => checkbox.checked = isSelecting);
            toggleSelectBtn.textContent = isSelecting ? 'Cancelar' : 'Selecionar';
            updateActionButtons();
        });

        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            rowCheckboxes.forEach(checkbox => checkbox.checked = isChecked);
            isSelecting = isChecked;
            toggleSelectBtn.textContent = isChecked ? 'Cancelar' : 'Selecionar';
            updateActionButtons();
        });

        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateActionButtons);
        });

        function updateActionButtons() {
            const selectedIds = getSelectedIds();
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

        gerarPagamentoBtn.addEventListener('click', () => {
            const selectedIds = getSelectedIds();
            sendToApi('api/create-payment', selectedIds);
        });

        aprovarTodosBtn.addEventListener('click', () => {
            const selectedIds = getSelectedIds();
            sendToApi('api/approved-all', selectedIds);
        });

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
                        text: 'Processo concluído! A página será recarregada para atualizar às informações.',
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (data.invoiceUrl) {
                                window.location.href = data.invoiceUrl;
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