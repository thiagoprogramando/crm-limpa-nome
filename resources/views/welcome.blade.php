@extends('layout')    
@section('content')
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
                                <div class="form-group mb-3">
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
@endsection
