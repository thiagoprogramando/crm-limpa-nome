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

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Preencha todas às informações para começar:</h5>

                    @if (Auth::user()->status == 1 && Auth::user()->type != 3)
                        <div class="col-12">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle me-1"></i>
                                Está tudo perfeito! Agora é com você!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif
                    
                    @if (Auth::user()->api_key != null && Auth::user()->wallet != null)
                        <div class="col-12">
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle me-1"></i>
                                Estamos na última etapa! Nos links abaixo, você pode enviar sua documentação para analisarmos?
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            @if(count($mydocuments) > 0)
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Pagamentos</h5>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Situação</th>
                                                        <th>Documento</th>
                                                        <th>Descrição</th>
                                                        <th class="text-center">Opções</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($mydocuments as $key => $myDocument)
                                                        <tr>
                                                            <td>
                                                                @switch($myDocument['status'])
                                                                    @case('NOT_SENT')
                                                                        Não enviado
                                                                        @break
                                                                    @case('PENDING')
                                                                        Em Análise
                                                                        @break
                                                                    @case('APPROVED')
                                                                        Aprovado
                                                                        @break
                                                                    @case('REJECTED')
                                                                        Rejeitado
                                                                        @break
                                                                    @default
                                                                @endswitch
                                                            </td>
                                                            <td>{{ $myDocument['title'] }}</td>
                                                            <td>Para enviar/reenviar esse documento utilize o botão ao lado.</td>
                                                            <td class="text-center">
                                                                @switch($myDocument['status'])
                                                                    @case('NOT_SENT')
                                                                    <a class="btn btn-primary" target="_blank" href="{{ $myDocument['onboardingUrl'] }}"><i class="bi bi-arrow-up-right-circle"></i></a>
                                                                        @break
                                                                    @case('PENDING')
                                                                        Em Análise
                                                                        @break
                                                                    @case('APPROVED')
                                                                        Aprovado
                                                                        @break
                                                                    @case('REJECTED')
                                                                        <a class="btn btn-primary" target="_blank" href="{{ $myDocument['onboardingUrl'] }}"><i class="far fa-paper-plane"></i></a>
                                                                        @break
                                                                    @default
                                                                @endswitch
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if (Auth::user()->status == 3 && empty(Auth::user()->api_key) && Auth::user()->type != 3)
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

                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Mantenha seus dados atualizados.</h5>

                    <form action="{{ route('update-user') }}" method="POST" class="row g-3">
                        @csrf

                        <input type="hidden" name="id" value="{{ Auth::user()->id }}">

                        <div class="col-12 col-md-6 col-lg-6 mb-1">
                            <div class="form-floating">
                                <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control" id="floatingName" placeholder="Nome:">
                                <label for="floatingName">Nome:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="text" name="cpfcnpj" value="{{ Auth::user()->cpfcnpj }}" class="form-control" id="floatingCpfcnpj" placeholder="CPF ou CNPJ:">
                                <label for="floatingCpfcnpj">CPF ou CNPJ:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="date" name="birth_date" value="{{ Auth::user()->birth_date }}" class="form-control" id="floatingDate" placeholder="Data de Aniversário:">
                                <label for="floatingDate">Data de Aniversário:</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="text" name="phone" value="{{ Auth::user()->phone }}" class="form-control" id="floatingPhone" placeholder="Telefone:">
                                <label for="floatingPhone">Telefone:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" id="floatingEmail" placeholder="Email:">
                                <label for="floatingEmail">Email:</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="number" name="postal_code" value="{{ Auth::user()->postal_code }}" onblur="consultaCEP()" class="form-control" id="floatingPostalCode" placeholder="CEP:">
                                <label for="floatingPostalCode">CEP:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="number" name="num" value="{{ Auth::user()->num }}" class="form-control" id="floatingNum" placeholder="N°:">
                                <label for="floatingNum">N°:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1">
                            <div class="form-floating">
                                <input type="text" name="address" value="{{ Auth::user()->address }}" class="form-control" id="floatingAddress" placeholder="Endereço:">
                                <label for="floatingAddress">Endereço:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="text" name="city" value="{{ Auth::user()->city }}" class="form-control" id="floatingCity" placeholder="Cidade:">
                                <label for="floatingCity">Endereço:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 col-lg-3 mb-1">
                            <div class="form-floating">
                                <input type="text" name="state" value="{{ Auth::user()->state }}" class="form-control" id="floatingState" placeholder="Estado:">
                                <label for="floatingState">Estado:</label>
                            </div>
                        </div>
                        
                        <input type="hidden" name="id" value="{{ Auth::user()->id }}">

                        <div class="col-12 col-md-3 col-lg-3 offset-md-9 offset-lg-9 d-grid gap-2 mb-1">
                            <button type="submit" class="btn btn-outline-success rounded-pill" type="button">Atualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection