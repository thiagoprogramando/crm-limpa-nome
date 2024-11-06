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

                        <div class="wrap d-md-flex" style="min-height: 70vh;">
                            <div class="img" style="background-image: url({{ asset('assets/login-form/images/bg_client.png') }}); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>

                            <div class="login-wrap p-4 p-md-5">
                                <div class="d-flex">
                                    <div class="w-100">
                                        <h3 class="mb-4">Bem-vindo (a)!</h3>
                                    </div>
                                    <div class="w-100">
                                        <p class="social-media d-flex justify-content-end">
                                            <a href="https://www.facebook.com/diegoduarteg7/" target="_blank" class="social-icon d-flex align-items-center justify-content-center"><span class="fa fa-facebook"></span></a>
                                            <a href="https://www.instagram.com/g7assessoria/" target="_blank" class="social-icon d-flex align-items-center justify-content-center"><span class="fa fa-instagram"></span></a>
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('logon.cliente') }}" method="POST" class="signin-form">
                                    @csrf
                                    @if (session('error'))
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <div class="form-group mb-3">
                                        <label class="label" for="cpfcnpj">CPF ou CNPJ:</label>
                                        <input type="number" name="cpfcnpj" class="form-control" placeholder="000.000.000-80" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="form-control btn btn-primary rounded submit px-3">Acessar</button>
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

