@extends('app.layout')
@section('title') Enviar Contrato: {{ $product->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Enviar Contrato: {{ $product->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Enviar Contrato</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="card">
            <div class="card-body m-0 p-3">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    @if (!empty(Auth::user()->token_wallet))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sale-tab" data-bs-toggle="tab" data-bs-target="#sale-justified" type="button" role="tab" aria-controls="sale" aria-selected="false" tabindex="-1">Gerar Contrato (Cliente)</button>
                        </li>
                    @endif
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="association-tab" data-bs-toggle="tab" data-bs-target="#association-justified" type="button" role="tab" aria-controls="association" aria-selected="false" tabindex="-1">Enviar Direto (Associação)</button>
                    </li>
                </ul>

                <div class="tab-content pt-2" id="myTabjustifiedContent">
                    @if (!empty(Auth::user()->token_wallet))
                        <div class="tab-pane fade active show" id="sale-justified" role="tabpanel" aria-labelledby="sale-tab">
                            <div class="row">
                                <form action="{{ route('created-client-sale') }}" method="POST" class="col-12 col-sm-12 col-md-5 col-lg-5 border-end p-md-3 p-lg-3 mb-5" id="formClient">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="seller_id" value="{{ Auth::user()->id }}">
                                    <div class="row">
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                            <p class="card-title  m-0 p-0">Dados do Cliente</p>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="name" placeholder="Nome:" value="{{ $user->name ?? '' }}" required>
                                                <label for="name">Nome:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="email" class="form-control" id="email" placeholder="E-mail:" value="{{ $user->email ?? '' }}">
                                                <label for="email">Email:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="phone" class="form-control phone" id="phone" placeholder="WhatsApp:" value="{{ $user->phone ?? '' }}" oninput="maskPhone(this)">
                                                <label for="phone">WhatsApp:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="cpfcnpj" class="form-control cpfcnpj" id="cpfcnpj" placeholder="CPF ou CNPJ:" value="{{ $user->cpfcnpj ?? '' }}" oninput="maskCpfCnpj(this)" required>
                                                <label for="cpfcnpj">CPF ou CNPJ:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="birth_date" class="form-control" id="birth_date" placeholder="Data Nascimento:" value="{{ $user->birth_date ?? '' }}" required>
                                                <label for="birth_date">Data Nascimento:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-12 offset-md-6 col-md-6 offset-lg-6 col-lg-6 mb-2">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-outline-primary mt-1">{{ $user ? 'Atualizar Cliente' : 'Incluir Cliente' }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                @if (isset($user))
                                    <form action="{{ route('created-payment-sale') }}" method="POST" id="formSale" class="col-12 col-sm-12 col-md-7 col-lg-7 p-md-3 p-lg-3 mb-5">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="client_id" value="{{ $user->id }}">
                                        <input type="hidden" name="seller_id" value="{{ Auth::user()->id }}">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <p class="card-title m-0 p-0">Dados de Pagamento</p>
                                            </div>
                                            <div class="col-7 col-sm-7 col-md-5 col-lg-5 mb-2">
                                                <div class="form-floating">
                                                    <select name="payment_method" class="form-select" id="payment_method" required>
                                                        <option selected="">Opções:</option>
                                                        <option value="PIX">Pix</option>
                                                        <option value="BOLETO">Boleto</option>
                                                        {{-- <option value="CREDIT_CARD">Cartão de Crédito</option> --}}
                                                    </select>
                                                    <label for="payment_method">Forma de Pagamento</label>
                                                </div>
                                            </div>
                                            <div class="col-5 col-sm-5 col-md-4 col-lg-4 mb-2">
                                                <div class="form-floating">
                                                    <input type="number" name="payment_installments" class="form-control" id="payment_installments" placeholder="Parcelas:" min="1">
                                                    <label for="payment_installments">Parcelas:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                            </div>
                                            <div class="col-7 col-sm-7 col-md-5 col-lg-5 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="installments[1][value]" class="form-control" id="installments[1][value]" placeholder="Valor da venda (Mín R$ {{ $product->value_min }}):" oninput="maskValue(this)" required>
                                                    <label for="installments[1][value]">Entrada (Mín R$ {{ $product->value_min }}):</label>
                                                </div>
                                            </div>
                                            <div class="col-5 col-sm-5 col-md-4 col-lg-4 mb-2">
                                                <div class="form-floating">
                                                    <input type="date" name="installments[1][due_date]" class="form-control" id="installments[1][due_date]" placeholder="Vencimento:" required>
                                                    <label for="installments[1][due_date]">Vencimento:</label>
                                                </div>
                                            </div>
                                            <div id="installmentsContainer" class="row m-0 p-0 mb-2">
                                                
                                            </div>
                                            <div class="col-12 col-sm-12 offset-md-5 col-md-4 offset-lg-5 col-lg-4 mb-2 d-grid">
                                                <button type="submit" class="btn btn-primary" type="button">Gerar</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="tab-pane fade @if (empty(Auth::user()->token_wallet)) active show @endif" id="association-justified" role="tabpanel" aria-labelledby="association-tab">
                        <div class="row">

                            <div class="col-6 col-sm-6 col-md-6 col-lg-6 mb-2">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                                    <button type="button" id="toggle-select" class="btn btn-outline-primary">Selecionar</button>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">Importar Por Excel</button>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Adicionar Nome</button>
                                    <button id="quanty-name" class="btn btn-sm btn-outline-primary">Selecionados: </button>
                                </div>
                            </div>

                            <div class="modal fade" id="filterModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('create-sale', ['product' => $product->id]) }}" method="GET">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Filtrar Pesquisa</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                        <div class="form-floating">
                                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o Nome:">
                                                            <label for="floatingName">Cliente:</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                        <div class="form-floating">
                                                            <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                                            <label for="floatingCreated_at">Data:</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                        <div class="form-floating">
                                                            <select name="status" class="form-select" id="status">
                                                                <option selected value="">Escolha uma opção:</option>
                                                                <option value="00">Pendente</option>
                                                                <option value="1">Pagamento confirmado</option>
                                                            </select>
                                                            <label for="status">Status</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                        <div class="form-floating">
                                                            <select name="label" class="form-select" id="label">
                                                                <option selected value="">Escolha uma opção:</option>
                                                                <option value="REPROTOCOLADO">Reprotocolado</option>
                                                            </select>
                                                            <label for="label">Tags</label>
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

                            <div class="modal fade" id="addModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('created-sale-association', ['product' => $product->id]) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Enviar Direto (Associação)</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                        <p>
                                                            <b class="text-danger">ATENÇÃO!</b> <br>
                                                            O Envio Direto exige o preenchimento correto das informações do cliente!
                                                        </p>
                                                    </div>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <div class="form-floating mb-2">
                                                            <input type="text" name="name" class="form-control" id="name" placeholder="Nome:" required="">
                                                            <label for="name">Nome:</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <div class="form-floating mb-2">
                                                            <input type="text" name="cpfcnpj" class="form-control" id="cpfcnpj" placeholder="CPF ou CNPJ:" oninput="mascaraCpfCnpj(this)" required="">
                                                            <label for="cpfcnpj">CPF ou CNPJ:</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer btn-group">
                                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                                <button type="submit" class="btn btn-primary">Enviar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="uploadModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('created-sale-excel', ['product' => $product->id]) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Enviar Por Excel</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                        <p>
                                                            <b class="text-danger">ATENÇÃO!</b> <br>
                                                            O Envio por Excel exige o preenchimento correto da  <b><a href="{{ asset('assets/files/planilha_limpa_nome.xlsm') }}" download>Planilha Modelo</a></b>, 
                                                            os campos marcados com <b class="text-danger">*</b> são obrigatórios!</b>
                                                        </p>
                                                    </div>
                                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                        <div class="form-floating">
                                                            <input type="file" name="file" class="form-control" id="file" placeholder="file:" accept=".xlsm" required>
                                                            <label for="file">Excel:</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer btn-group">
                                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                                <button type="submit" class="btn btn-primary">Enviar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <div id="action-buttons" class="d-none btn-group mb-3">
                                    @if(Auth::user()->type == 1)
                                        <button id="aproved-all" class="btn btn-sm btn-primary">Aprovar Todos</button>
                                    @endif
                                    <button id="create-payment" class="btn btn-sm btn-outline-primary">Gerar Pagamento</button>
                                </div>

                                <table class="table table-sm table-hover" id="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Cliente</th>
                                            <th scope="col">Consultor</th>
                                            <th>Produto</th>
                                            <th>Detalhes</th>
                                            <th class="text-center">Contrato</th>
                                            <th class="text-center">Pagamento</th>
                                            <th class="text-center">Opções</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sales as $sale)
                                            <tr>
                                                <td title="{{ $sale->client->name }}">
                                                    <input type="checkbox" class="row-checkbox" value="{{ $sale->id }}"> {{ $sale->id }} -
                                                    {{ implode(' ', array_slice(explode(' ', $sale->client->name), 0, 2)) }} <br>
                                                    <span class="badge bg-dark">CPF/CNPJ: {{ $sale->client->cpfcnpjLabel() }}</span>
                                                </td>
                                                <td title="{{ $sale->seller->name }}">
                                                    {{ implode(' ', array_slice(explode(' ', $sale->seller->name), 0, 2)) }} <br>
                                                    <span class="badge bg-dark">{{ $sale->seller->email }}</span>
                                                </td>
                                                <td>
                                                    <p class="m-0 p-0">
                                                        {{ implode(' ', array_slice(explode(' ', $sale->product->name), 0, 2)) }} <br>
                                                    </p>
                                                    <span>R$ {{ number_format($sale->totalInvoices(), 2, ',', '.') }}</span> 
                                                </td>
                                                <td>
                                                    <div class="text-start">
                                                        <span class="badge bg-primary" title="{{ $sale->list->name }}">
                                                            {{ $sale->list->name }} <br> {{ $sale->list->statusProtocolLabel() }}
                                                        </span>
                                                        @isset($sale->label) 
                                                            <span class="badge bg-warning">
                                                                {{ $sale->label }}
                                                            </span> 
                                                        @endisset
                                                        @isset($sale->guarantee)
                                                            <span class="badge bg-success">
                                                                Garantia: {{ \Carbon\Carbon::parse($sale->guarantee)->format('d/m/Y') }}
                                                            </span>
                                                        @endisset
                                                    </div>                     
                                                </td>
                                                <td class="text-center">
                                                    @if ( $sale->statusContractLabel() == 'Assinado')
                                                        <span class="badge bg-success">
                                                            <a title="Contrato" href="{{ env('APP_URL').'view-contract/'.$sale->id }}" target="_blank" class="text-white">Acessar</a>
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning" title="Copiar URL" onclick="onClip('{{ env('APP_URL') }}view-contract/{{ $sale->id }}')">
                                                            <i class="ri-file-edit-line"></i> Copiar Link do Contrato
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{ $sale->statusPaymentLabel() }} <br>
                                                    <span class="badge bg-primary">{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <form action="{{ route('deleted-sale') }}" method="POST" class="delete">
                                                        @csrf
                                                        <input type="hidden" name="uuid" value="{{ $sale->uuid }}"> 
                                                        <div class="btn-group" role="group">
                                                            <a title="Ver Dados da Venda" href="{{ route('view-sale', ['uuid' => $sale->uuid]) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                                                            @if ($sale->status == 1 && Auth::user()->type == 1)
                                                                <a title="Reprotocolar" href="{{ route('reprotocol-sale', ['id' => $sale->id]) }}" class="btn btn-outline-primary btn-sm"><i class="bx bx-check-shield"></i></a>
                                                            @endif
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">Excluir</button>
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
        </div>
    </section>

    <script>
        $(document).ready(function () {
            const MAX_installments = 18;

            function updateInstallmentsField() {

                const paymentMethod         = $('#payment_method').val();
                const installmentsField     = $('#payment_installments');
                const valueLabel            = $('#floatingValueLabel');
                const installmentsContainer = $('#installmentsContainer');

                if (paymentMethod === 'PIX' || paymentMethod === 'BOLETO') {
                    installmentsField.attr('max', MAX_installments);
                    installmentsField.attr('min', 1);
                    installmentsField.val(1);
                    installmentsField.prop('disabled', false);
                    valueLabel.text('Entrada (Mín R$ {{ $product->value_min }})');

                    generateinstallmentsFields(parseInt(installmentsField.val()));
                } else if (paymentMethod === 'CREDIT_CARD') {
                    installmentsField.attr('min', 1);
                    installmentsField.attr('max', MAX_installments);
                    installmentsField.prop('disabled', false);
                    valueLabel.text('Valor Mín (R$ {{ $product->value_min }})');

                    installmentsContainer.html('');
                }

                installmentsField.off('input').on('input', function () {
                    let val = parseInt(installmentsField.val(), 10);
                    if (val < 1) val = 1;
                    if (val > MAX_installments) val = MAX_installments;
                    installmentsField.val(val);

                    if (paymentMethod === 'PIX' || paymentMethod === 'BOLETO') {
                        generateinstallmentsFields(val);
                    }
                });
            }

            function generateinstallmentsFields(qtd) {
                const container = $('#installmentsContainer');
                container.html('');

                for (let i = 2; i <= qtd; i++) {
                    const inputGroup = `
                        <div class="col-7 col-sm-7 col-md-5 col-lg-5 mb-2">
                            <div class="form-floating">
                                <input type="text" name="installments[${i}][value]" class="form-control" placeholder="Parcela N° ${i}" oninput="maskValue(this)" required>
                                <label>Parcela N° ${i}</label>
                            </div>
                        </div>
                        <div class="col-5 col-sm-5 col-md-4 col-lg-4 mb-2">
                            <div class="form-floating">
                                <input type="date" name="installments[${i}][due_date]" class="form-control" required>
                                <label>Vencimento</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        </div>
                    `;
                    container.append(inputGroup);
                }
            }

            $('#payment_method').change(function () {
                updateInstallmentsField();
            });

            updateInstallmentsField();
        });

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
                    sendToApi('{{ url('api/approved-all-payment') }}', selectedIds);
                });
            }

            if (btnCreatePayment) {
                btnCreatePayment.addEventListener('click', () => {
                    const selectedIds = getSelectedIds();
                    sendToApi('{{ url('api/create-payment') }}', selectedIds);
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

                var user_id = @json(Auth::user()->id);
                var user_token = @json(Auth::user()->getToken());
                var product_id = @json($product->id);

                fetch(route, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        ids, 
                        user_id: user_id,
                        product_id: product_id,
                        token: user_token
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
                                if (data.data.invoiceUrl) {
                                    window.open(data.data.invoiceUrl, '_blank', 'noopener,noreferrer');
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
                    console.log(error);
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