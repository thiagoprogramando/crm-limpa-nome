<!doctype html>
<html lang="pt-br">
    <head>
        <title>{{ env('APP_NAME') }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="{{ asset('assets/dashboard/img/favicon.png') }}" rel="icon">

        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="{{ asset('assets/login-form/css/style.css') }}">

        <script src="{{ asset('assets/dashboard/js/sweetalert.js')}}"></script>
	</head>
	<body>

        <section class="ftco-section">
            <div class="container">

                <div class="row justify-content-center">
                    <div class="col-sm-12 col-md-12 col-lg-12">

                        <div class="wrap p-5">
                            <div class="w-100">
                                <h3 class="mb-4">Bem-vindo(a), complete com os seus dados.</h3>
                            </div>
                                
                            <form action="{{ route('create-sale') }}" method="POST" class="signin-form row">
                                @csrf
                                <div class="form-group col-sm-12 col-md-12 col-lg-12 mb-3">
                                    @if (session('error'))
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                </div>

                                <input type="hidden" name="product" value="{{ $id_product }}">
                                <input type="hidden" name="id_seller" value="{{ $id_seller }}">
                                <input type="hidden" name="value" value="{{ $value }}">

                                <div class="form-group col-sm-12 col-md-6 col-lg-6 mb-3">
                                    <input type="text" name="name" class="form-control" placeholder="Nome completo:" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-3 col-lg-3 mb-3">
                                    <input type="number" name="cpfcnpj" class="form-control" placeholder="CPF ou CNPJ:" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-3 col-lg-3 mb-3">
                                    <input type="text" name="birth_date" class="form-control" placeholder="Data de Nascimento:" oninput="mascaraData(this)" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-3 col-lg-3 mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="Email:" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-3 col-lg-3 mb-3">
                                    <input type="text" name="phone" class="form-control" placeholder="WhatsApp:" oninput="mascaraTelefone(this)" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-6 mb-3">
                                    <select name="payment" class="form-control" required>
                                        <option selected>Escolha entre uma das opções de pagamento disponível:</option>
                                        @foreach ($payments as $payment)
                                            <option value="{{ $payment->id }}">{{ $payment->methodLabel() }} - {{ $payment->installments }}X @if($payment->value_rate > 0) com juros @else sem juros @endif</option>  
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-sm-12 col-md-2 col-lg-2 mb-3">
                                    <input type="number" name="postal_code" class="form-control" placeholder="CEP:" onblur="consultaCEP()" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-1 col-lg-1 mb-3">
                                    <input type="number" name="num" class="form-control w-100" placeholder="N°:" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-3 col-lg-3 mb-3">
                                    <input type="text" name="address" class="form-control" placeholder="Endereço:" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-3 col-lg-3 mb-3">
                                    <input type="text" name="city" class="form-control" placeholder="Cidade:" required>
                                </div>
                                <div class="form-group col-sm-12 col-md-3 col-lg-3 mb-3">
                                    <input type="text" name="state" class="form-control" placeholder="Estado:" required>
                                </div>

                                <div class="form-group col-sm-12 col-md-12 col-lg-12">
                                    <button type="submit" class="form-control btn btn-primary rounded submit px-3">Enviar dados</button>
                                </div>
                            </form>  
                        </div>

                    </div>
                </div>
                
            </div>
        </section>

        <script src="{{ asset('assets/login-form/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/login-form/js/popper.js') }}"></script>
        <script src="{{ asset('assets/login-form/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/login-form/js/main.js') }}"></script>
        <script src="{{ asset('assets/dashboard/js/mask.js') }}"></script>
        <script>
            @if(session('error'))
                Swal.fire({
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    timer: 2000
                })
            @endif
            
            @if(session('success'))
                Swal.fire({
                    title: 'Sucesso!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    timer: 2000
                })
            @endif
        </script>
	</body>
</html>

