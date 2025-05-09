<!doctype html>
<html lang="pt-br">
    <head>
        <title>{{ env('APP_NAME') }} - {{ env('APP_DESCRIPTION') }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="{{ asset('login_template/images/favicon.png') }}" rel="icon">
        <link href="https://fonts.gstatic.com" rel="preconnect">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
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
                                    <div class="w-100 text-center">
                                        <h3 class="mb-4">{{ env('APP_NAME') }}</h3>
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

                                <form action="{{ route('logon') }}" method="POST" class="signin-form">
                                    @csrf
                                    @if (session('error'))
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <div class="form-group mb-2">
                                        <label class="label" for="name">Email:</label>
                                        <input type="email" name="email" class="form-control" placeholder="joao@xxxxx.com" required>
                                    </div>
                                    <div class="form-group mb-2 position-relative">
                                        <label class="label" for="password">Senha:</label>
                                        <div class="input-group">
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Senha" required>
                                            <button type="button" class="btn btn-outline-secondary toggle-password" onclick="togglePassword()">
                                                <i id="eyeIcon" class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <button type="submit" class="form-control btn btn-primary rounded submit px-3">Acessar</button>
                                    </div>
                                    <div class="form-group">
                                        <div class="text-center">
                                            <a href="{{ route('forgout') }}">Recuperar Conta</a>
                                        </div>
                                    </div>
                                </form>
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
        <script>
            function togglePassword() {
                const passwordField = document.getElementById("password");
                const eyeIcon = document.getElementById("eyeIcon");

                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    eyeIcon.classList.remove("fa-eye");
                    eyeIcon.classList.add("fa-eye-slash");
                } else {
                    passwordField.type = "password";
                    eyeIcon.classList.remove("fa-eye-slash");
                    eyeIcon.classList.add("fa-eye");
                }
            }
        </script>
	</body>
</html>

