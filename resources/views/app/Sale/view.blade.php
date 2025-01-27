@extends('app.layout')
@section('title') Dados da Venda: N° {{ $sale->id }} - {{ $sale->user->name }} @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Dados da Venda: N° {{ $sale->id }} - {{ $sale->user->name }}</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Dados da Venda: N° {{ $sale->id }}</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                @if(Auth::user()->type == 1) 
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updatedModal">Alterar dados</button>
                @endif
                @if ($sale->payment !== 'ENVIO MANUAL')
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal">Adicionar Fatura</button>
                @endif
            </div>

            <div class="modal fade" id="updatedModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('updated-sale') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $sale->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Dados da venda</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <select name="status" class="form-select" id="floatingSeller">
                                                <option value="">Status:</option>
                                                <option @selected($sale->status == 1) value="1">Aprovado</option>
                                                <option @selected($sale->status == 2) value="2">Assinado</option>
                                                <option @selected($sale->status == 3) value="3">Pendente</option>
                                            </select>
                                            <label for="floatingSeller">Status</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
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
                                </div>
                            </div>
                            <div class="modal-footer btn-group">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="invoiceModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('create-invoice') }}" method="POST">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                            <input type="hidden" name="product_id" value="{{ $sale->product->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Dados da Fatura</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Valor:" oninput="mascaraReal(this)" required>
                                            <label for="floatingValue">Valor:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="date" name="due_date" class="form-control" id="floatingDueDate" placeholder="Vencimento:" required>
                                            <label for="floatingDueDate">Vencimento:</label>
                                        </div>
                                    </div>
                                    @if (Auth::user()->type == 1)
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <select name="wallet" class="form-select" id="floatingSeller">
                                                    <option value="">Comissão (opcional):</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->wallet }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="floatingSeller">Consultores</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="commission" class="form-control" id="floatingCommission" placeholder="Comissão (valor):" oninput="mascaraReal(this)">
                                                <label for="floatingCommission">Comissão (valor):</label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                        <div class="form-floating">
                                            <select name="billingType" class="form-select" id="floatingBillingType" required>
                                                <option value="">Método de Pagamento:</option>
                                                <option value="PIX">Pix</option>
                                                <option value="BOLETO">Boleto</option>
                                                <option value="CREDIT_CARD">Cartão de Crédito</option>
                                            </select>
                                            <label for="floatingBillingType">Métodos</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group">
                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary">Criar Fatura</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Faturas associadas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
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
                                        <th scope="row">{{ $invoice->id }}</th>
                                        <td>{{ $invoice->description }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                        <td class="text-center">R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                        <td class="text-center">R$ {{ number_format($invoice->commission, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ $invoice->statusLabel() }}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-primary text-light"><i class="bi bi-upc"></i></a>
                                                <a href="{{ route('send-default-whatsapp', ['id' => $invoice->id]) }}" class="btn btn-success text-light confirm"><i class="bi bi-whatsapp"></i></a>
                                                @if($invoice->num <> 1)
                                                    <a href="{{ route('delete-invoice', ['id' => $invoice->id]) }}" class="btn btn-danger text-light confirm"><i class="bi bi-trash"></i></a>
                                                @endif
                                            </div>
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