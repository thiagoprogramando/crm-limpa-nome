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
                            <div class="img" style="background-image: url({{ asset('login_template/images/bg.png') }}); min-height: 500px;"></div>

                            <div class="login-wrap p-4 p-md-5">
                                <div class="d-flex mb-3">
                                    <div class="w-100 text-center">
                                        <h3>Recuperação de Conta</h3>
                                    </div>
                                </div>

                                @if ($errors->any())
                                    <div class="alert border-danger alert-dismissible fade show" role="alert">
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert border-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if(!empty($token))
                                    <form action="{{ route('recovery-password', ['token' => $token]) }}" method="POST" class="signin-form">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <input type="text" name="password" class="form-control" placeholder="Nova senha:" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <input type="password" name="password_confirmed" class="form-control" placeholder="Confirme a senha:" required>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="form-control btn btn-primary rounded submit px-3">Atualizar</button>
                                        </div>
                                    </form>
                                @else
                                    <form action="{{ route('forgout-password') }}" method="POST" class="signin-form">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <input type="email" name="email" class="form-control" placeholder="E-mail:">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="form-control btn btn-primary rounded submit px-3">Recuperar</button>
                                        </div>
                                    </form>
                                @endif
                                <p class="text-center">Já possui uma conta? <a href="{{ route('login') }}">Acessar</a></p>
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
	</body>
</html>

