<!doctype html>
<html lang="pt-br">
    <head>
        <title>{{ env('APP_NAME') }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="{{ asset('login_template/images/favicon.png') }}" rel="icon">
        <link href="https://fonts.gstatic.com" rel="preconnect">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('login_template/css/style.css') }}">
	</head>
	<body>

        <section class="ftco-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12 col-lg-10">
                        <div class="wrap d-md-flex">
                            <div class="img" style="background-image: url({{ asset('login_template/images/bg.png') }});"></div>

                            <div class="login-wrap p-4 p-md-5">
                                <div class="d-flex">
                                    <div class="w-100">
                                        <h3 class="mb-4">Faça parte</h3>
                                    </div>
                                </div>

                                <form action="{{ route('registrer-user') }}" method="POST" class="signin-form">
                                    @csrf
                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (session('success'))
                                        <div class="alert border-success alert-dismissible fade show" role="alert">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    
                                    <input type="hidden" name="filiate" value="{{ isset($id) ? $id : '' }}">
                                    <input type="hidden" name="fixed_cost" value="{{ isset($fixed_cost) ? $fixed_cost : '' }}">
                                    <div class="form-group mb-3">
                                        <input type="text" name="name" class="form-control" placeholder="Nome:" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="number" name="cpfcnpj" id="cpfcnpj" class="form-control" placeholder="CPF/CNPJ:" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="email" name="email" class="form-control" placeholder="Email:" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="number" name="phone" class="form-control" placeholder="Telefone:" required>
                                    </div>
                                    <div class="form-group d-md-flex">
                                        <div class="w-100 text-left">
                                            <label class="checkbox-wrap checkbox-primary mb-0">Concordo com os termos de uso <input type="checkbox" name="terms" checked> <span class="checkmark"></span> </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="form-control btn btn-primary rounded submit px-3">Cadastrar-me</button>
                                    </div>
                                </form>
                                <p class="text-center">Já é um membro? <a href="{{ route('login') }}">Acessar</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script src="{{ asset('login_template/js/jquery.min.js') }}"></script>
        <script src="{{ asset('login_template/js/popper.js') }}"></script>
        <script src="{{ asset('login_template/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('login_template/js/main.js') }}"></script>
        <script src="{{ asset('assets/js/mask.js') }}"></script>
	</body>
</html>

