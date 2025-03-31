@extends('client.app.layout')
@section('title') Faturas @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Faturas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app-cliente') }}">Início</a></li>
                <li class="breadcrumb-item active">Faturas</li>
            </ol>
        </nav>
    </div>
    
    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Fatura</th>
                                        <th class="text-center" scope="col">Status</th>
                                        <th class="text-center" scope="col">Vencimento</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        <tr>
                                            <th scope="row">{{ $invoice->id }}</th>
                                            <td>
                                                {{ $invoice->name }} <br>
                                                <span class="badge bg-dark">{{ $invoice->description }}</span>
                                            </td>
                                            <td class="text-center">{{ $invoice->statusLabel() }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    @if ($invoice->status !== 1)
                                                        <a href="{{ route('update-invoice', ['id' => $invoice->token_payment, 'value' => $invoice->value, 'dueDate' => now()->addDays(1), 'callback' => 5]) }}" class="btn btn-outline-primary">SEGUNDA VIA</a>
                                                        @if ($invoice->type == 1)
                                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#couponModal{{ $invoice->id }}">CUPOM</button>
                                                        @endif
                                                    @endif
                                                    <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-primary text-light">ACESSA</a>
                                                </div>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="couponModal{{ $invoice->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('add-coupon') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">CUPOM:</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="name" class="form-control" id="floatingName" placeholder="Código:">
                                                                        <label for="floatingName">Código:</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer btn-group">
                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                                            <button type="submit" class="btn btn-success">Adicionar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            {{ $invoices->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection