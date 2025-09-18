@extends('app.layout')
@section('title') Gestão de Links @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Gestão de Links</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Gestão de Links</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Novo Link</button>
                    <button type="button" id="gerarExcel" class="btn btn-sm btn-outline-primary">Excel</button>
                </div>

                <div class="modal fade" id="registerModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('created-link') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalhes:</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="title" class="form-control" id="title" placeholder="Valor:">
                                                <label for="title">Título (Opcional):</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <textarea class="form-control" id="description" style="height: 100px"></textarea>
                                                <label for="description">Legenda/Descrição (Opcional):</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="value" class="form-control" id="value" placeholder="Valor:" oninput="maskValue(this)" required>
                                                <label for="value">Valor:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <select name="product_id" class="form-select" id="product_id" required>
                                                    <option selected="" value="">Produtos:</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>  
                                                    @endforeach
                                                </select>
                                                <label for="product_id">Produto</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <select name="user_id" class="form-select" id="user_id">
                                                    <option selected="" value="">Consultores:</option>
                                                    @foreach ($sellers as $seller)
                                                        <option value="{{ $seller->id }}">{{ $seller->name }}</option>  
                                                    @endforeach
                                                </select>
                                                <label for="user_id">Consultor</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-7 col-lg-7 mb-2">
                                            <div class="form-floating">
                                                <select name="payment_method" class="form-select" id="payment_method" required>
                                                    <option selected="" value="">Opções:</option>
                                                    <option value="PIX">PIX</option>
                                                    <option value="BOLETO">Boleto</option>  
                                                </select>
                                                <label for="payment_method">Método de Pagamento</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5 col-lg-5 mb-2">
                                            <div class="form-floating">
                                                <input type="number" name="payment_installments" class="form-control" id="payment_installments" placeholder="Parcelas:" required>
                                                <label for="payment_installments">Parcelas:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-2 d-none" id="dynamic-fields">
                                            <div class="row" id="installments-container"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer btn-group">
                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-primary">Gerar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body m-0 p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">DADOS</th>
                                        <th scope="col">DETALHES</th>
                                        <th class="text-center" scope="col">OPÇÕES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($links as $link)
                                        <tr>
                                            <th scope="row">#{{ $link->id }}</th>
                                            <td>
                                                {{ $link->title ?? $link->user->name }} <br>
                                                <span class="badge bg-dark">Produto: {{ $link->product->name ?? 'Sem produto associado!'}}</span>
                                            </th>
                                            <td>
                                                {{ $link->payment_method }} em {{ $link->payment_installments }}x<br>
                                                <span class="badge bg-dark">Valor: R$ {{ number_format($link->value, 2, ',', '.') }}</span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('deleted-link', ['uuid' => $link->uuid]) }}" method="POST" class="delete">
                                                    @csrf
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-primary" onclick="onClip('{{ route('create-external-sale', ['uuid' => $link->uuid]) }}')"><i class="bx bx-copy"></i></button>
                                                        <button type="submit" class="btn btn-outline-primary"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            {{ $links->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const paymentMethod         = document.getElementById('payment_method');
        const installmentsInput     = document.getElementById('payment_installments');
        const dynamicFields         = document.getElementById('dynamic-fields');
        const installmentsContainer = document.getElementById('installments-container');
        const fixedCosts            = @json($sellers->pluck('fixed_cost', 'id'));
        const authFixedCost         = {{ Auth::user()->fixed_cost }};

        function generateInstallmentFields() {
            const method        = paymentMethod.value;
            const installments  = parseInt(installmentsInput.value) || 0;
            const selectedUser  = document.getElementById('user_id').value;

            installmentsContainer.innerHTML = '';

            if (method === 'CREDIT_CARD') {
                dynamicFields.classList.add('d-none');
                return;
            }

            if ((method === 'PIX' || method === 'BOLETO') && installments > 0) {
                
                dynamicFields.classList.remove('d-none');

                let minValue = authFixedCost;
                if (selectedUser && fixedCosts[selectedUser]) {
                    minValue = fixedCosts[selectedUser];
                }

                for (let i = 1; i <= installments; i++) {
                    
                    const valueCol = document.createElement('div');
                    valueCol.className = 'col-12 col-md-7 col-lg-7 mb-2';

                    if (i === 1) {
                        valueCol.innerHTML = `
                            <div class="form-floating">
                                <input type="text" name="installments[${i}][value]" class="form-control" 
                                    placeholder="Valor:" oninput="maskValue(this)" 
                                    data-min="${minValue}">
                                <label>Valor Par/N° ${i} (mínimo: R$ ${minValue})</label>
                            </div>
                        `;
                    } else {
                        valueCol.innerHTML = `
                            <div class="form-floating">
                                <input type="text" name="installments[${i}][value]" class="form-control" 
                                    placeholder="Valor:" oninput="maskValue(this)">
                                <label>Valor Par/ N° ${i}</label>
                            </div>
                        `;
                    }

                    const dueCol = document.createElement('div');
                    dueCol.className = 'col-12 col-md-5 col-lg-5 mb-2';
                    dueCol.innerHTML = `
                        <div class="form-floating">
                            <input type="date" name="installments[${i}][due_date]" class="form-control" placeholder="Vencimento:">
                            <label>Vencimento Par/N° ${i}</label>
                        </div>
                    `;

                    installmentsContainer.appendChild(valueCol);
                    installmentsContainer.appendChild(dueCol);
                }
            } else {
                dynamicFields.classList.add('d-none');
            }
        }

        paymentMethod.addEventListener('change', generateInstallmentFields);
        installmentsInput.addEventListener('input', generateInstallmentFields);
        document.getElementById('user_id').addEventListener('change', generateInstallmentFields);
    </script>
@endsection