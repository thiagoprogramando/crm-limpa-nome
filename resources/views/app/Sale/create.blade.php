@extends('app.layout')
@section('title') Lançamento de venda: {{ $product->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Lançamento de venda: {{ $product->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Lançamento de venda</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Preencha todos os dados da Venda.</h5>
        
                        <form action="{{ route('create-sale') }}" method="POST" class="row g-3">
                            @csrf
                            
                            <input type="hidden" name="product" value="{{ $product->id }}">

                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o nome do Cliente:" required>
                                    <label for="floatingName">Nome:</label>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-3 col-lg-3 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCnpj" placeholder="Informe o CPF ou CNPJ do Cliente:" oninput="mascaraCpfCnpj(this)" required>
                                    <label for="floatingCpfCnpj">CPF ou CNPJ:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-3 col-lg-3 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="birth_date" class="form-control" id="floatingBirth_date" placeholder="Data Nascimento:" oninput="mascaraData(this)" required>
                                    <label for="floatingBirth_date">Data Nascimento:</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="email" class="form-control" id="floatingEmail" placeholder="Informe o email do Cliente:" required>
                                    <label for="floatingEmail">Email:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="phone" class="form-control" id="floatingPhone" placeholder="Informe o whatsapp do Cliente:" oninput="mascaraTelefone(this)" required>
                                    <label for="floatingPhone">WhatsApp:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Informe o valor da venda:" oninput="mascaraReal(this)" required>
                                    <label for="floatingValue">Informe o valor da venda:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                <div class="form-floating">
                                    <select name="payment" class="form-select" id="floatingSelect" required>
                                        <option selected="">Escolha entre uma das opções de pagamento disponível:</option>
                                        @foreach ($payments as $payment)
                                            <option value="{{ $payment->id }}">{{ $payment->methodLabel() }} - {{ $payment->installments }}X @if($payment->value_rate > 0) com juros @else sem juros @endif</option>  
                                        @endforeach
                                    </select>
                                    <label for="floatingSelect">Forma de Pagamento</label>
                                </div>
                            </div>

                            @if ($product->address)
                                <div class="col-12 col-md-2 col-lg-2 mb-1">
                                    <div class="form-floating">
                                        <input type="number" name="postal_code" onblur="consultaCEP()" class="form-control" id="floatingPostal" placeholder="CEP:" required>
                                        <label for="floatingPostal">CEP:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-1 col-lg-1 mb-1">
                                    <div class="form-floating">
                                        <input type="number" name="num" class="form-control" id="floatingNumber" placeholder="N°:" required>
                                        <label for="floatingNumber">N°:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-lg-3 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="address" class="form-control" id="floatingAddress" placeholder="Endereço:" required>
                                        <label for="floatingAddress">Endereço:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-lg-3 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="city" class="form-control" id="floatingCity" placeholder="Cidade:" required>
                                        <label for="floatingCity">Cidade:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-lg-3 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="state" class="form-control" id="floatingState" placeholder="Estado:" required>
                                        <label for="floatingState">Estado:</label>
                                    </div>
                                </div>
                            @endif
                            
                            @if(Auth::user()->level == 5)
                                <div class="col-12 col-md-4 col-lg-4 offset-md-8 offset-lg-8 mb-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="wallet_off" id="wallet_off">
                                        <label class="form-check-label" for="wallet_off">Descontar da carteira <span class="badge bg-success">Saldo: {{ Auth::user()->wallet_off }}</span></label>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12 col-md-4 col-lg-4 offset-md-8 offset-lg-8 mb-1 d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-outline-success rounded-pill" type="button">Gerar Venda</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection