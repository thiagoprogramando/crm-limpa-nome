@extends('app.layout')
@section('title') Enviar Contrato: {{ $product->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Enviar Contrato: {{ $product->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Enviar Contrato</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-2">

                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-justified" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Enviar nome</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-justified" type="button" role="tab" aria-controls="home" aria-selected="true">Saiba Mais</button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="myTabjustifiedContent">
                            <div class="tab-pane fade" id="home-justified" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row p-3">
                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 text-center">
                                        <iframe class="embed-responsive-item w-100 h-100" src="https://www.youtube.com/embed/i55n31X-2LQ?si=ffJgVnnpEBDvKaOm" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                                        <p class="card-title">Como Gerar uma Venda para o Cliente Final pelo Seu Painel?</p>
                                        <p>
                                            Preencha os dados necessários no painel para iniciar a venda. 
                                            <b>
                                                O cliente receberá automaticamente a prévia do contrato e o link de pagamento (valor referente a entrada).
                                            </b> 
                                            Após a confirmação do pagamento, você receberá  sua comissão, que poderá ser 
                                            solicitada imediatamente para saque, de forma instantânea. <br><br>
                                            <b class="text-danger">Atenção:</b> Vendas no cartão de crédito tem prazo de saque maiores, <a href="">saiba mais</a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade active show mt-3" id="profile-justified" role="tabpanel" aria-labelledby="profile-tab">
                                <form id="consultaHub" class="row">
                                    <div class="col-12 col-md-3 col-lg-3 mb-1">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="cpfcnpj" placeholder="Informe o CPF ou CNPJ do Cliente:" oninput="mascaraCpfCnpj(this)">
                                            <label for="cpfcnpj">CPF ou CNPJ:</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3 col-lg-3 mb-1">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="nascimento" placeholder="Data Nascimento:">
                                            <label for="nascimento">Aniversário/Abertura:</label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-3 col-lg-3 mb-1">
                                        <button type="button" onclick="consulta()" class="btn btn-lg btn-primary mt-1">Consultar</button>
                                    </div>
                                </form>

                                <form action="{{ route('create-sale') }}" method="POST" id="formSale" class="row d-none g-3">
                                    @csrf
                                    
                                    <input type="hidden" name="product" value="{{ $product->id }}">
                                    <input type="hidden" name="id_seller" value="{{ Auth::user()->id }}">

                                    <div class="row">
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-7 row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                <p class="card-title me-4">Dados do Cliente</p>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o nome do Cliente:" required>
                                                    <label for="floatingName">Nome:</label>
                                                </div>
                                            </div>
        
                                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="email" class="form-control" id="floatingEmail" placeholder="Informe o email do Cliente:" required>
                                                    <label for="floatingEmail">Email:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="phone" class="form-control" id="floatingPhone" placeholder="Informe o whatsapp do Cliente:" oninput="mascaraTelefone(this)" required>
                                                    <label for="floatingPhone">WhatsApp:</label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCnpj" placeholder="Informe o CPF ou CNPJ do Cliente:" oninput="mascaraCpfCnpj(this)" required>
                                                    <label for="floatingCpfCnpj">CPF ou CNPJ:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating">
                                                    <input type="date" name="birth_date" class="form-control" id="floatingBirth_date" placeholder="Data Nascimento:" required>
                                                    <label for="floatingBirth_date">Data Nascimento:</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-12 col-md-6 col-lg-5 card">
                                            <div class="row">
                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                    <p class="card-title me-4">Dados de Pagamento</p>
                                                </div>
                                                <div class="col-12 col-sm-12 col-md-7 col-lg-7 mb-1">
                                                    <div class="form-floating">
                                                        <select name="payment" class="form-select" id="floatingSelect" required>
                                                            <option selected="">Opções:</option>
                                                            <option value="PIX">Boleto/Pix</option>
                                                            <option value="CREDIT_CARD">Cartão de Crédito</option>
                                                        </select>
                                                        <label for="floatingSelect">Forma de Pagamento</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-12 col-md-5 col-lg-5 mb-1">
                                                    <div class="form-floating">
                                                        <input type="number" name="installments" class="form-control" id="floatingInstallments" placeholder="Parcelas:" min="1">
                                                        <label for="floatingInstallments">Parcelas:</label>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-12 col-md-7 col-lg-7 mb-2">
                                                    <div class="form-floating">
                                                        <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Valor da venda (Mín R$ {{ Auth::user()->fixed_cost }}):" oninput="mascaraReal(this)" required>
                                                        <label for="floatingValue">Entrada (Mín R$ {{ Auth::user()->fixed_cost }}):</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-12 col-md-5 col-lg-5 mb-2">
                                                    <div class="form-floating">
                                                        <input type="date" name="dueDate" class="form-control" id="floatingdueDate" placeholder="Vencimento:">
                                                        <label for="floatingdueDate">Vencimento:</label>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-2">
                                                    <div class="form-floating">
                                                        <input type="text" name="value_total" class="form-control" id="floatingValue" placeholder="Valor Total da venda:" oninput="mascaraReal(this)" required>
                                                        <label for="floatingValue">Valor Total da venda:</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-2 mt-2 btn-group">
                                                    <a href="{{ route('createsale', ['id' => $product->id]) }}" title="Recarregar" class="btn btn-outline-primary"><i class="bi bi-arrow-counterclockwise"></i></a>
                                                    <button type="submit" class="btn btn-primary" type="button">Enviar</button>
                                                </div>
                                            </div>
                                        </div>
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
    <script>
        $(document).ready(function() {
            
            function updateInstallmentsField() {

                var paymentMethod = $('#floatingSelect').val();
                var installmentsField = $('#floatingInstallments');

                if (paymentMethod === 'PIX') {
                    installmentsField.attr('max', 1);
                    installmentsField.val(1);
                    installmentsField.prop('disabled', true);
                } else if (paymentMethod === 'CREDIT_CARD') {
                    installmentsField.attr('min', 1);
                    installmentsField.attr('max', 6);
                    installmentsField.prop('disabled', false);
                    
                    installmentsField.on('input', function() {
                        var value = parseInt(installmentsField.val(), 10);
                        if (value < 1) {
                            installmentsField.val(1);
                        } else if (value > 6) {
                            installmentsField.val(6);
                        }
                    });
                }
            }

            updateInstallmentsField();

            $('#floatingSelect').change(function() {
                updateInstallmentsField();
            });
        });

    </script>
@endsection