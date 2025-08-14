@extends('layout')    
@section('content')
    <section class="ftco-section">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-10">

                    <div class="wrap d-md-flex">
                        <div class="img" style="background-image: url({{ asset('login-form/images/bg.png') }});"></div>

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
@endsection

