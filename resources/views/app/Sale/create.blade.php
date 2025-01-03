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
                    <div class="card-body p-2">

                        <ul class="nav nav-tabs d-flex" id="myTabjustified" role="tablist">
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100 active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-justified" type="button" role="tab" aria-controls="home" aria-selected="true">Como funciona</button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-justified" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Realizar Venda</button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="myTabjustifiedContent">
                            <div class="tab-pane fade active show" id="home-justified" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row p-3">
                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 text-center" style="background-color: #063986 !important;">
                                        <img src="{{ asset('assets/dashboard/img/document.png') }}" class="w-50 m-5">
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                                        <p class="card-title">Como Gerar uma Venda para seu Cliente Final através do seu Painel?</p>
                                        <p> 
                                            Ao preencher os dados, <b>o cliente receberá automaticamente o contrato, 
                                            seguido do pagamento do valor da venda final.</b> Após o pagamento, 
                                            você será comissionado, e o saque da comissão poderá ser solicitado 
                                            imediatamente, sendo realizado de forma instantânea.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profile-justified" role="tabpanel" aria-labelledby="profile-tab">

                                <form id="consultaHub" class="row g-3">
                                    <div class="col-12 col-md-3 col-lg-3 mb-1">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="cpfcnpj" placeholder="Informe o CPF ou CNPJ do Cliente:" oninput="mascaraCpfCnpj(this)" required>
                                            <label for="cpfcnpj">CPF ou CNPJ:</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3 col-lg-3 mb-1">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="nascimento" placeholder="Data Nascimento:" required>
                                            <label for="nascimento">Data Nascimento:</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3 col-lg-3 mb-1">
                                        <button type="button" onclick="consulta()" class="btn btn-lg btn-primary mt-1" type="button">Consultar</button>
                                    </div>
                                </form>

                                <form action="{{ route('create-sale') }}" method="POST" id="formSale" class="row g-3 d-none">
                                    @csrf
                                    
                                    <input type="hidden" name="product" value="{{ $product->id }}">
                                    <input type="hidden" name="id_seller" value="{{ Auth::user()->id }}">
        
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
                                            <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Valor da venda (Mín R$ {{ Auth::user()->fixed_cost }}):" oninput="mascaraReal(this)" required>
                                            <label for="floatingValue">Valor da venda (Mín R$ {{ Auth::user()->fixed_cost }}):</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4 mb-1">
                                        <div class="form-floating">
                                            <select name="payment" class="form-select" id="floatingSelect" required>
                                                <option selected="">Opções:</option>
                                                @foreach ($payments as $payment)
                                                    <option value="{{ $payment->id }}">{{ $payment->methodLabel() }} - {{ $payment->installments }}X @if($payment->value_rate > 0) com juros @else sem juros @endif</option>  
                                                @endforeach
                                            </select>
                                            <label for="floatingSelect">Forma de Pagamento</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-2 col-lg-2 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="coupon" class="form-control" id="floatingCoupon" placeholder="CUPOM:">
                                            <label for="floatingCoupon">CUPOM:</label>
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
        
                                    <div class="col-12 offset-md-8 col-md-4 offset-lg-8 col-lg-4 mb-3 mt-3 btn-group">
                                        <a href="{{ route('createsale', ['id' => $product->id]) }}" title="Recarregar" class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise"></i></a>
                                        <button type="submit" class="btn btn-success" type="button">Enviar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('assets/dashboard/js/hub.js') }}"></script>
@endsection