@extends('app.layout')
@section('title') Carteira Digital: Dados @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Carteira Digital: Dados</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Carteira Digital: Dados</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12 col-sm-12 offset-md-3 col-md-6 offset-lg-3 col-lg-6">
            <div class="card">
                <div class="card-body p-3">
                    <a href="https://www.asaas.com/onboarding/createAccount?customerSignUpOriginChannel=HOME" class="text-decoration-none">
                        <h3 class="lead text-center font-weight-bold">Carteira Digital: Assas</h3>
                    </a>
                    <p class="text-justify" style="font-size: 14px;">
                        Nosso banco parceiro é o Assas. Para ter acesso aos módulos avançados de comissão dentro 
                        da plataforma, você precisará de uma <strong>Carteira Digital</strong>. 
                    </p>
                    <p class="text-justify" style="font-size: 14px;">
                        Para criar sua Carteira, basta 
                        <a href="https://www.asaas.com/onboarding/createAccount?customerSignUpOriginChannel=HOME" class="text-primary font-weight-bold">
                            abrir uma conta Assas (clicando aqui)
                        </a>. 
                        Após o envio de documentos e aprovação do cadastro, será necessário 
                        <a href="#" target="_blank" class="text-primary font-weight-bold">
                            gerar os tokens de permissão (clicando aqui)
                        </a>.
                    </p>
                    <p class="text-justify" style="font-size: 12px;">
                        <strong>Atenção:</strong> Qualquer dúvida, fale conosco via <a href="#" class="text-primary font-weight-bold">Suporte WhatsApp</a>.
                    </p>

                    <form action="{{ route('send-assas-token') }}" method="POST" class="row">
                        @csrf
                        <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                            <div class="form-floating">
                                <input type="text" name="token_wallet" value="{{ Auth::user()->token_wallet }}" class="form-control" id="token_wallet" placeholder="Wallet:" @readonly(!empty(Auth::user()->token_wallet))>
                                <label for="token_wallet">Wallet:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                            <div class="form-floating">
                                <input type="text" name="token_key" value="{{ Auth::user()->token_key }}" class="form-control" id="token_key" placeholder="API KEY:" @readonly(!empty(Auth::user()->token_key))>
                                <label for="token_key">API KEY:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-12 d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary" @disabled(!empty(Auth::user()->token_key) && !empty(Auth::user()->token_wallet))>Válidar Tokens</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection