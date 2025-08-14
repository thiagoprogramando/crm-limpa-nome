@extends('layout')    
@section('content')

    <section class="ftco-section">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-10">

                    <div class="wrap d-md-flex">
                        <div class="img" style="background-image: url({{ asset('login-form/images/bg.png') }}); min-height: 500px;"></div>

                        <div class="login-wrap p-4 p-md-5">
                            <div class="d-flex">
                                <div class="w-100">
                                    <h3 class="mb-4">Recuperação</h3>
                                </div>
                                <div class="w-100"></div>
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
@endsection

