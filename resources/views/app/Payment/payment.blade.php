@extends('app.layout')
@section('title') Pagamentos @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Pagamentos</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Extrato de Pagamentos</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>

                <div class="card p-2">
                    <div class="card-body">
                        <h5 class="card-title">Pagamentos</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th class="text-center" scope="col">Valor</th>
                                        <th class="text-center" scope="col">Status</th>
                                        <th class="text-center" scope="col">Vencimento</th>
                                        <th class="text-center" scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payments as $payment)
                                        <tr>
                                            <th scope="row">{{ $payment->id }}</th>
                                            <td>{{ $payment->name }}</td>
                                            <td class="text-center">R$ {{ $payment->value }}</td>
                                            <td class="text-center">{{ $payment->statusLabel() }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</td>
                                            <td class="text-center btn-group">
                                                @if ($payment->type == 1 && $payment->status !== 1)
                                                    <button type="button" class="btn btn-dark text-light" data-bs-toggle="modal" data-bs-target="#couponModal{{ $payment->id }}"><i class="bi bi-percent"></i> Aplicar CUPOM</button>
                                                @endif
                                                @if(!empty(Auth::user()->wallet) && $payment->status !== 1)
                                                    <a href="{{ route('payMonthly', ['id' => $payment->id]) }}" class="btn btn-success text-light">
                                                        <i class="bi bi-credit-card"></i> Pagar
                                                    </a>
                                                @endif
                                                <a href="{{ $payment->url_payment }}" target="_blank" class="btn btn-primary text-light">
                                                    <i class="bi bi-arrow-up-right-circle"></i> Acessar
                                                </a>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="couponModal{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('add-coupon') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="invoice_id" value="{{ $payment->id }}">
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
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection