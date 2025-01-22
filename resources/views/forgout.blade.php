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
                                        <h3 class="mb-4">Recuperação</h3>
                                        
                                    </div>
                                    <div class="w-100">
                                        {{-- <p class="social-media d-flex justify-content-end">
                                            <a href="https://www.facebook.com/diegoduarteg7/" target="_blank" class="social-icon d-flex align-items-center justify-content-center"><span class="fa fa-facebook"></span></a>
                                            <a href="https://www.instagram.com/g7assessoria/" target="_blank" class="social-icon d-flex align-items-center justify-content-center"><span class="fa fa-instagram"></span></a>
                                        </p> --}}
                                    </div>
                                </div>

                                @if($code != null)
                                    <form action="{{ route('update-password') }}" method="POST" class="signin-form">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <input type="text" name="code" class="form-control" placeholder="Código:" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <input type="text" name="password" class="form-control" placeholder="Nova senha:" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <input type="text" name="repeat_password" class="form-control" placeholder="Confirme a senha:" required>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="form-control btn btn-primary rounded submit px-3">Atualizar</button>
                                        </div>
                                    </form>
                                @else
                                    <form action="{{ route('send-code-password') }}" method="POST" class="signin-form">
                                        @csrf
                                        <div class="form-group mb-3">
                                            <input type="email" name="email" class="form-control" placeholder="Email:" required>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="form-control btn btn-primary rounded submit px-3">Solicitar código</button>
                                        </div>
                                    </form>
                                @endif
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
        <script>
            @if(session('error'))
                Swal.fire({
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    timer: 5000
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
        </script>
	</body>
</html>

