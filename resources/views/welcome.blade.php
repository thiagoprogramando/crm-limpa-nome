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
                    <div class="col-md-12 col-lg-10">
                        <div class="wrap d-md-flex">
                            <div class="img" style="background-image: url({{ asset('assets/login-form/images/bg.png') }});"></div>

                            <div class="login-wrap p-4 p-md-5">
                                <div class="d-flex">
                                    <div class="w-100">
                                        <h3 class="mb-4">Entrar</h3>
                                    </div>
                                </div>

                                <form action="{{ route('logon') }}" method="POST" class="signin-form">
                                    @csrf
                                    @if (session('error'))
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <div class="form-group mb-1">
                                        <label class="label" for="name">Email:</label>
                                        <input type="email" name="email" class="form-control" placeholder="joao@xxxxx.com" required>
                                    </div>
                                    <div class="form-group mb-1 position-relative">
                                        <label class="label" for="password">Senha:</label>
                                        <div class="input-group">
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Senha" required>
                                            <button type="button" class="btn btn-outline-secondary toggle-password" onclick="togglePassword()">
                                                <i id="eyeIcon" class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="form-control btn btn-primary rounded submit px-3">Acessar</button>
                                    </div>
                                    <div class="form-group d-md-flex">
                                        <div class="text-md-right">
                                            <a href="{{ route('forgout') }}">Esqueci minha senha</a>
                                        </div>
                                    </div>
                                </form>
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
        <script>
            @if(session('error'))
                Swal.fire({
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    timer: 5000
                })
            @endif

            @if(session('info'))
                Swal.fire({
                    title: 'Atenção!',
                    text: '{{ session('info') }}',
                    icon: 'info',
                    timer: 2000
                })
            @endif
            
            @if(session('success'))
                Swal.fire({
                    title: 'Sucesso!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    timer: 5000
                })
            @endif

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

