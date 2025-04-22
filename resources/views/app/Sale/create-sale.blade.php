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
        <div class="card">
            <div class="card-body m-0 p-3">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-justified" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Enviar nome</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-justified" type="button" role="tab" aria-controls="home" aria-selected="true">Saiba Mais</button>
                    </li>
                </ul>

                <div class="tab-content pt-2" id="myTabjustifiedContent">
                    <div class="tab-pane fade active show" id="profile-justified" role="tabpanel" aria-labelledby="profile-tab">
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
                                            <input type="text" name="email" class="form-control" id="email" placeholder="E-mail:" value="{{ $user->email ?? '' }}" required>
                                            <label for="email">Email:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="phone" class="form-control phone" id="phone" placeholder="WhatsApp:" value="{{ $user->phone ?? '' }}" oninput="mascaraTelefone(this)" required>
                                            <label for="phone">WhatsApp:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-2">
                                        <div class="form-floating">
                                            <input type="text" name="cpfcnpj" class="form-control cpfcnpj" id="cpfcnpj" placeholder="CPF ou CNPJ:" value="{{ $user->cpfcnpj ?? '' }}" oninput="mascaraCpfCnpj(this)" required>
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
                                                    <option value="CREDIT_CARD">Cartão de Crédito</option>
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
                                                <input type="text" name="installments[1][value]" class="form-control" id="floatingValue" placeholder="Valor da venda (Mín R$ {{ Auth::user()->fixed_cost }}):" oninput="mascaraReal(this)" required>
                                                <label for="floatingValue" id="floatingValueLabel">Entrada (Mín R$ {{ $product->value_min }}):</label>
                                            </div>
                                        </div>
                                        <div class="col-5 col-sm-5 col-md-4 col-lg-4 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="installments[1][due_date]" class="form-control" id="floatingdueDate" placeholder="Vencimento:">
                                                <label for="floatingdueDate">Vencimento:</label>
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
                                <input type="text" name="installments[${i}][value]" class="form-control" placeholder="Parcela N° ${i}" oninput="mascaraReal(this)" required>
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
    </script>
@endsection