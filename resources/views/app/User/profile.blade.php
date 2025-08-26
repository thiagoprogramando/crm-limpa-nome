@extends('app.layout')
@section('title') Perfil @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Perfil</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
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
                    Você está quase lá! É necessário filiar-se <a href="{{ route('createMonthly', ['id' => Auth::user()->id]) }}"> ao {{ env('APP_NAME') }} clicando aqui!</a>
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

        @if ((Auth::user()->api_key !== null && Auth::user()->wallet !== null && Auth::user()->type != 6) && Auth::user()->status !== 1)
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 text-center" style="background-color: #063986 !important;">
                                <img src="{{ asset('assets/img/document.png') }}" class="w-50 m-5">
                            </div>

                            <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                                @if(count($mydocuments) > 0)
                                    <h5 class="card-title">Envie seus documentos</h5>
                                    <p>Pendentes</p>
                                    <p><b>Importante!</b> Para você conseguir movimentar seu saldo, é necessário enviar seus documentos.</p>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Documento</th>
                                                    <th class="text-center">Opções</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($mydocuments as $key => $myDocument)
                                                    <tr>
                                                        <td>{{ $myDocument['title'] }}</td>
                                                        <td class="text-center">
                                                            @switch($myDocument['status'])
                                                                @case('NOT_SENT')
                                                                <a class="btn btn-primary" target="_blank" href="{{ $myDocument['onboardingUrl'] }}"><i class="bi bi-arrow-up-right-circle"></i> Enviar</a>
                                                                    @break
                                                                @case('PENDING')
                                                                    Em Análise
                                                                    @break
                                                                @case('APPROVED')
                                                                    Aprovado
                                                                    @break
                                                                @case('REJECTED')
                                                                    <a class="btn btn-primary" target="_blank" href="{{ $myDocument['onboardingUrl'] }}"><i class="far fa-paper-plane"> Reenviar (Documentação anterior Rejeitada)</i></a>
                                                                    @break
                                                                @default
                                                            @endswitch
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>   
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Mantenha seus dados atualizados.</h5>
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-3 col-lg-3 text-center">
                            <div class="profile-photo">
                                @if(Auth::user()->photo)
                                    <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="User Photo" class="img-thumbnail w-50">
                                @else
                                    <img src="{{ asset('assets/img/profile_black.png') }}" alt="Default Photo" class="img-thumbnail w-50">
                                @endif
                            </div>
            
                            <button class="btn btn-dark mt-3" id="change-photo-button">Trocar foto de perfil</button>
            
                            <form action="{{ route('updated-user') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form" class="d-none">
                                @csrf
                                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                                <input type="file" name="photo" id="photo-input" accept="image/*" onchange="document.getElementById('photo-upload-form').submit();">
                            </form>
                        </div>

                        <form action="{{ route('updated-user') }}" method="POST" class="col-12 col-sm-12 col-md-9 col-lg-9 row">
                            @csrf
                            <input type="hidden" name="id" value="{{ Auth::user()->id }}">

                            <div class="col-7 row">
                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control" id="floatingName" placeholder="Nome:">
                                        <label for="floatingName">Nome:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" id="floatingEmail" placeholder="Email:">
                                        <label for="floatingEmail">Email:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="phone" value="{{ Auth::user()->phone }}" class="form-control" id="floatingPhone" placeholder="Telefone:">
                                        <label for="floatingPhone">Telefone:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="cpfcnpj" value="{{ Auth::user()->cpfcnpj }}" class="form-control" id="floatingCpfcnpj" placeholder="CPF ou CNPJ:">
                                        <label for="floatingCpfcnpj">CPF ou CNPJ:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="date" name="birth_date" value="{{ Auth::user()->birth_date }}" class="form-control" id="floatingDate" placeholder="Data de Aniversário:">
                                        <label for="floatingDate">Data de Aniversário:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                    <div class="form-floating">
                                        <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Senha:">
                                        <label for="floatingPassword">Senha:</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-5 row">
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="number" name="postal_code" value="{{ Auth::user()->postal_code }}" onblur="consultaCEP()" class="form-control" id="floatingPostalCode" placeholder="CEP:">
                                        <label for="floatingPostalCode">CEP:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="number" name="num" value="{{ Auth::user()->num }}" class="form-control" id="floatingNum" placeholder="N°:">
                                        <label for="floatingNum">N°:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="address" value="{{ Auth::user()->address }}" class="form-control" id="floatingAddress" placeholder="Endereço:">
                                        <label for="floatingAddress">Endereço:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="city" value="{{ Auth::user()->city }}" class="form-control" id="floatingCity" placeholder="Cidade:">
                                        <label for="floatingCity">Cidade:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="state" value="{{ Auth::user()->state }}" class="form-control" id="floatingState" placeholder="Estado:">
                                        <label for="floatingState">Estado:</label>
                                    </div>
                                </div>
                                <div class="col-12 d-grid gap-2 mb-1">
                                    <button type="submit" class="btn btn-primary">Atualizar</button>
                                </div>
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
</script>
@endsection