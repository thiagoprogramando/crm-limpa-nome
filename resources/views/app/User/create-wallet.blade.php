@extends('app.layout')
@section('title') Carteira Digital: Dados @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Carteira Digital: Dados</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Carteira Digital: Dados</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-5 col-lg-5">
                            <a href="https://www.asaas.com/onboarding/createAccount?customerSignUpOriginChannel=HOME" class="text-decoration-none">
                                <h3 class="lead text-center font-weight-bold">Carteira Digital: Assas</h3>
                            </a>
                            <p class="text-justify" style="font-size: 14px;">
                                Nosso banco parceiro é o Assas. Para ter acesso aos módulos avançados de comissão dentro 
                                da plataforma, você precisará de uma <strong>Carteira Digital</strong>. 
                            </p>
                            <p class="text-justify" style="font-size: 14px;">
                                Para criar sua Wallet, basta 
                                <a href="https://www.asaas.com/onboarding/createAccount?customerSignUpOriginChannel=HOME" class="text-primary font-weight-bold">
                                    abrir uma conta Assas (clicando aqui)
                                </a>. 
                                Após o envio de documentos e aprovação do cadastro, será necessário 
                                <a href="#" target="_blank" class="text-primary font-weight-bold">
                                    gerar os tokens de permissão (clicando aqui)
                                </a>.
                            </p>
                            <p class="text-justify" style="font-size: 12px;">
                                <strong>Atenção:</strong> Qualquer dúvida, fale conosco via <a href="#" class="text-primary font-weight-bold">chat online</a>.
                            </p>
                        </div>                        

                        <form action="{{ route('update-user') }}" method="POST" class="col-12 col-sm-12 col-md-7 col-lg-7 row">
                            @csrf
                            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="wallet" value="{{ Auth::user()->wallet }}" class="form-control" id="floatingWallet" placeholder="Wallet:" @readonly(!empty(Auth::user()->wallet))>
                                    <label for="floatingWallet">Wallet:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                <div class="form-floating">
                                    <input type="text" name="api_key" value="{{ Auth::user()->api_key }}" class="form-control" id="floatingApiKey" placeholder="API KEY:" @readonly(!empty(Auth::user()->api_key))>
                                    <label for="floatingApiKey">API KEY:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 col-lg-12 d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary">Válidar Tokens</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 p-2">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                            <iframe class="embed-responsive-item w-100" height="315" src="https://www.youtube.com/embed/YzTzVFr_veE?si=i86D1u3ByVYKH6DA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                        <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                            <iframe class="embed-responsive-item w-100" height="315" src="https://www.youtube.com/embed/F4LGvoejQcc?si=DEoFpIzY28wpVzHk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection