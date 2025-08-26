@extends('app.layout')
@section('title') Cobrança: {{ $invoice->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Cobrança: {{ $invoice->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Cobrança: {{ $invoice->name }}</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="card">
            <div class="card-body m-0 p-3">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-justified" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Dados da Cobrança</button>
                    </li>
                </ul>

                <div class="tab-content pt-2" id="myTabjustifiedContent">
                    <div class="tab-pane fade active show" id="profile-justified" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="row">
                            <form action="{{ route('updated-invoice') }}" method="POST" class="col-12 col-sm-12 col-md-5 col-lg-5">
                                @csrf
                                <input type="hidden" name="id" value="{{ $invoice->id }}">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" placeholder="Fatura:" value="{{ $invoice->name ?? '' }}" readonly>
                                            <label for="name">Fatura:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" class="form-control real" id="value" placeholder="CPF ou CNPJ:" value="{{ $invoice->value ?? '' }}" readonly>
                                            <label for="value">Valor:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="date" name="due_date" class="form-control" id="due_date" placeholder="Vencimento:" value="{{ $invoice->due_date ?? '' }}">
                                            <label for="due_date">Vencimento:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 d-grid mb-2">
                                        <div class="btn-group">
                                            <a href="{{ route('view-sale', ['id' => $invoice->sale->id]) }}" class="btn btn-outline-primary">Voltar</a>
                                            <button type="submit" class="btn btn-outline-primary">Atualizar</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection