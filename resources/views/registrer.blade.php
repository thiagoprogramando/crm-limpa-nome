<!doctype html>
<html lang="pt-br">
    <head>
        <title>{{ env('APP_NAME') }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="{{ asset('assets/dashboard/img/logo.png') }}" rel="icon">

        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="{{ asset('assets/login-form/css/style.css') }}">
	</head>
	<body>

        <section class="ftco-section">
            <div class="container">

                <div class="row justify-content-center">
                    <div class="col-md-12 col-lg-10">

                        <div class="wrap d-md-flex">
                            <div class="img" style="background-image: url({{ asset('assets/login-form/images/bg.png') }});"></div>

                            <div class="login-wrap p-4 p-md-5">
                                <div class="d-flex">
                                    <div class="w-100">
                                        <h3 class="mb-4">Cadastre-se</h3>
                                        
                                    </div>
                                    <div class="w-100">
                                        <p class="social-media d-flex justify-content-end">
                                            <a href="https://www.facebook.com/diegoduarteg7/" target="_blank" class="social-icon d-flex align-items-center justify-content-center"><span class="fa fa-facebook"></span></a>
                                            <a href="https://www.instagram.com/grupo7assessoria" target="_blank" class="social-icon d-flex align-items-center justify-content-center"><span class="fa fa-instagram"></span></a>
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('registrer-user') }}" method="POST" class="signin-form">
                                    @csrf
                                    @if (session('error'))
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <div class="form-group mb-3">
                                        <input type="text" name="name" class="form-control" placeholder="Nome:" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="text" name="cpfcnpj" class="form-control" placeholder="CPF ou CNPJ:" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="email" name="email" class="form-control" placeholder="Email:" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="password" name="password" class="form-control" placeholder="Senha:" required>
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

        <script src="{{ asset('assets/login-form/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/login-form/js/popper.js') }}"></script>
        <script src="{{ asset('assets/login-form/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/login-form/js/main.js') }}"></script>
	</body>
</html>
