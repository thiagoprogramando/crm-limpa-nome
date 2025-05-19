@extends('app.layout')
@section('title') Perfil @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Perfil</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Perfil</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">

        @if (Auth::user()->status == 1)
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-1"></i>
                    Está tudo perfeito! Agora é com você! <a href="{{ route('app') }}">Começar a vender</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if (Auth::user()->status == 3 && Auth::user()->months() == 0)
            <div class="col-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-1"></i>
                    Você está quase lá! É necessário filiar-se <a href="{{ route('create-monthly') }}"> ao {{ env('APP_NAME') }} clicando aqui!</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(empty(Auth::user()->postal_code) || empty(Auth::user()->address) || empty(Auth::user()->city) || empty(Auth::user()->state) || empty(Auth::user()->num))
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-octagon me-1"></i>
                    Complete os dados do seu endereço e contatos abaixo!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Mantenha seus dados atualizados.</h5>
                    <div class="row align-items-start">
                        <div class="col-12 col-sm-12 col-md-3 col-lg-3 text-center">
                            <div class="profile-photo">
                                @if(Auth::user()->photo)
                                    <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="User Photo" class="img-thumbnail w-50">
                                @else
                                    <img src="{{ asset('assets/img/profile_black.png') }}" alt="Default Photo" class="img-thumbnail w-50">
                                @endif
                            </div>
            
                            <button class="btn btn-dark mt-3 mb-3" id="change-photo-button">Trocar foto de perfil</button>
            
                            <form action="{{ route('update-user') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form" class="d-none">
                                @csrf
                                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                                <input type="file" name="photo" id="photo-input" accept="image/*" onchange="document.getElementById('photo-upload-form').submit();">
                            </form>
                        </div>

                        <form action="{{ route('update-user') }}" method="POST" class="col-12 col-sm-12 col-md-9 col-lg-9">
                            @csrf
                            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-7 col-lg-7">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control" id="floatingName" placeholder="Nome:">
                                                <label for="floatingName">Nome:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" id="floatingEmail" placeholder="Email:">
                                                <label for="floatingEmail">Email:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="cpfcnpj" value="{{ Auth::user()->cpfcnpj }}" oninput="maskCpfCnpj(this)" class="form-control cpfcnpj" id="cpfcnpj" placeholder="CPF ou CNPJ:">
                                                <label for="cpfcnpj">CPF ou CNPJ:</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="phone" value="{{ Auth::user()->phone }}" oninput="maskPhone(this)" class="form-control" id="phone" placeholder="Telefone:">
                                                <label for="phone">Telefone:</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="birth_date" value="{{ Auth::user()->birth_date }}" class="form-control" id="floatingDate" placeholder="Data de Aniversário:">
                                                <label for="floatingDate">Data de Aniversário:</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="password" class="form-control" id="password" placeholder="Senha:">
                                                <label for="password">Nova Senha:</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="password" name="confirmpassword" class="form-control" id="confirmPassword" placeholder="Confirme a Senha:">
                                                <label for="confirmPassword">Confirme a Senha:</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-5 col-lg-5">
                                    <div class="row">
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="number" name="postal_code" value="{{ Auth::user()->postal_code }}" onblur="consultAddress()" class="form-control" id="postal_code" placeholder="CEP:">
                                                <label for="postal_code">CEP:</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="number" name="num" value="{{ Auth::user()->num }}" class="form-control" id="num" placeholder="N°:">
                                                <label for="num">N°:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="address" value="{{ Auth::user()->address }}" class="form-control" id="address" placeholder="Endereço:">
                                                <label for="address">Endereço:</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="city" value="{{ Auth::user()->city }}" class="form-control" id="city" placeholder="Cidade:">
                                                <label for="city">Cidade:</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="state" value="{{ Auth::user()->state }}" class="form-control" id="state" placeholder="Estado:">
                                                <label for="state">Estado:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-grid gap-2 mb-2">
                                            <button type="submit" class="btn btn-primary">Atualizar</button>
                                        </div>
                                    </div>
                                </div>

                                @if (Auth::user()->type == 99)
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-6 col-md-7 col-lg-7 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="company_name" value="{{ Auth::user()->company_name }}" class="form-control" id="company_name" placeholder="Nome Fantasia:">
                                                    <label for="company_name">Nome Fantasia:</label>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-5 col-lg-5 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="company_cpfcnpj" value="{{ Auth::user()->company_cpfcnpj }}" class="form-control cpfcnpj" id="company_cpfcnpj" oninput="maskCpfCnpj(this)" placeholder="CNPJ:">
                                                    <label for="company_cpfcnpj">CNPJ:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-7 col-lg-7 mb-2">
                                                <div class="form-floating">
                                                    <input type="email" name="company_email" value="{{ Auth::user()->company_email }}" class="form-control" id="company_email" placeholder="E-mail:">
                                                    <label for="company_email">E-mail:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-5 col-lg-5 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="company_phone" value="{{ Auth::user()->company_phone }}" class="form-control" id="company_phone phone" oninput="maskPhone(this)" placeholder="Telefone:">
                                                    <label for="company_phone">Telefone:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="company_address" value="{{ Auth::user()->company_address }}" class="form-control" id="company_address" placeholder="Endereço:">
                                                    <label for="company_address">Endereço:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 d-grid gap-2 mb-2">
                                                <button type="submit" class="btn btn-primary">Salvar</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    document.getElementById('change-photo-button').addEventListener('click', function() {
        document.getElementById('photo-input').click();
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener("change", function() {
                this.closest("form").submit();
            });
        });
    });
</script>
@endsection