@extends('app.layout')
@section('title') Venda: {{ $sale->client->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Venda: {{ $sale->client->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Venda: {{ $sale->client->name }}</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="card">
            <div class="card-body m-0 p-3">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-justified" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Dados da Venda</button>
                    </li>
                </ul>

                <div class="tab-content pt-2" id="myTabjustifiedContent">
                    <div class="tab-pane fade active show" id="profile-justified" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="row">
                            <form action="{{ route('created-client-sale') }}" method="POST" class="col-12 col-sm-12 col-md-5 col-lg-5 border-end">
                                @csrf
                                <input type="hidden" name="seller_id" value="{{ Auth::user()->id }}">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <p class="card-title  m-0 p-0">Dados do Cliente</p>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Nome:" value="{{ $sale->client->name ?? '' }}" required>
                                            <label for="name">Nome:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="email" class="form-control" id="email" placeholder="E-mail:" value="{{ $sale->client->email ?? '' }}" required>
                                            <label for="email">Email:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="phone" class="form-control phone" id="phone" placeholder="WhatsApp:" value="{{ $sale->client->phone ?? '' }}" oninput="mascaraTelefone(this)" required>
                                            <label for="phone">WhatsApp:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="cpfcnpj" class="form-control cpfcnpj" id="cpfcnpj" placeholder="CPF ou CNPJ:" value="{{ $sale->client->cpfcnpj ?? '' }}" oninput="mascaraCpfCnpj(this)" disabled>
                                            <label for="cpfcnpj">CPF ou CNPJ:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="date" name="birth_date" class="form-control" id="birth_date" placeholder="Data Nascimento:" value="{{ $sale->client->birth_date ?? '' }}" disabled>
                                            <label for="birth_date">Data Nascimento:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 offset-md-6 col-md-6 offset-lg-6 col-lg-6 mb-2">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-outline-primary mt-1">{{ $sale->client ? 'Atualizar Cliente' : 'Incluir Cliente' }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="col-12 col-sm-12 col-md-7 col-lg-7 row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <p class="card-title m-0 p-0">Dados de Pagamento</p>
                                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createdModal">Adicionar Cobrança</button>

                                    <div class="modal fade" id="createdModal" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('created-invoice') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                                    <input type="hidden" name="product_id" value="{{ $sale->product_id }}">
                                                    <input type="hidden" name="client_id" value="{{ $sale->client_id }}">
                                                    <input type="hidden" name="seller_id" value="{{ $sale->seller_id }}">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Dados da Cobrança</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
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
                                                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                                                <div class="form-floating">
                                                                    <input type="text" name="value" class="form-control" id="value" placeholder="Valor:" oninput="maskValue(this)" required>
                                                                    <label for="value">Valor:</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                                                <div class="form-floating">
                                                                    <input type="date" name="due_date" class="form-control" id="due_date" placeholder="Vencimento:" required>
                                                                    <label for="due_date">Vencimento:</label>
                                                                </div>
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
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Fatura</th>
                                                    <th>Detalhes</th>
                                                    <th>Valor</th>
                                                    <th class="text-center">Opções</th>
                                                </tr>
                                            </thead>
                                            <tbody id="paymentTable">
                                                @foreach ($sale->invoices as $invoice)
                                                    <tr>
                                                        <td>{{ $invoice->name }}</td>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                {{ $invoice->statusLabel() }}
                                                            </span>
                                                            <a class="badge bg-dark" href="{{ $invoice->payment_url }}" target="_blank">
                                                                Link de Pagamento
                                                            </a>
                                                        </td>
                                                        <td>{{ number_format($invoice->value, 2, ',', '.') }}</td>
                                                        <td>
                                                            <form action="{{ route('deleted-invoice') }}" method="POST" class="d-grid delete">
                                                                @csrf
                                                                <input type="hidden" name="id" value="{{ $invoice->id }}">
                                                                <div class="btn-group">
                                                                    <a href="{{ route('view-invoice', ['id' => $invoice->id]) }}" class="btn btn-outline-primary btn-sm">
                                                                        Editar
                                                                    </a>
                                                                    @if (Auth::user()->type === 1 && $invoice->status !== 1)
                                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                            Excluir
                                                                        </button>
                                                                    @endif
                                                                </div>
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
                </div>
            </div>
        </div>
    </section>
@endsection