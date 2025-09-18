<!doctype html>
<html lang="pt-br">
    <head>
        <title>{{ env('APP_NAME') }}</title>
        
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="{{ asset('login-form/css/style.css') }}">

        <script src="{{ asset('assets/js/sweetalert.js')}}"></script>
	</head>
	<body>

        @yield('content')

        <script src="{{ asset('login-form/js/jquery.min.js') }}"></script>
        <script src="{{ asset('login-form/js/popper.js') }}"></script>
        <script src="{{ asset('login-form/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('login-form/js/main.js') }}"></script>
        <script src="{{ asset('assets/js/mask.js') }}"></script>
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