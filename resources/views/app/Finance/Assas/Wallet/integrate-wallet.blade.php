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
                                <input type="text" name="token_wallet" value="{{ Auth::user()->token_wallet }}" class="form-control" id="token_wallet" placeholder="Wallet:">
                                <label for="token_wallet">Wallet:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-12 mb-1">
                            <div class="form-floating">
                                <input type="text" name="token_key" value="{{ Auth::user()->token_key }}" class="form-control" id="token_key" placeholder="API KEY:">
                                <label for="token_key">API KEY:</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-12 d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary">Válidar Tokens</button>
                        </div>
                    </form>

                    @if (Auth::user()->type == 99 || Auth::user()->type == 1)
                        <hr>
                        <a href="https://www.asaas.com/onboarding/createAccount?customerSignUpOriginChannel=HOME" class="text-decoration-none">
                            <h3 class="lead text-center font-weight-bold">WebHook</h3>
                        </a>
                        <p class="text-justify" style="font-size: 14px;">
                            Um Webhook é uma forma automatizada de enviar informações entre sistemas quando certos eventos ocorrem. 
                            Quando você ativa um Webhook, ele passará a enviar requisições para o endereço configurado sempre que determinado evento acontecer. 
                            Essa requisição incluirá informações sobre o evento e o recurso envolvido.
                        </p>

                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createModal">Adicionar WebHook</button>

                        <div class="modal fade" id="createModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('created-webhook') }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Preencha todos os dados!</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                    <div class="form-floating">
                                                        <input type="text" name="name" class="form-control" id="name" placeholder="Ex: Express integração" required>
                                                        <label for="name">Nome:</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                    <div class="form-floating">
                                                        <input type="text" name="url" class="form-control" id="url" placeholder="URL:" value="{{ env('APP_URL') }}api/webhook-assas" readonly>
                                                        <label for="url">URL:</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer btn-group">
                                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                            <button type="submit" class="btn btn-primary">Enviar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Detalhes</th>
                                        <th scope="col">URL</th>
                                        <th scope="col" class="text-center">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     @foreach ($webhooks as $webhook)
                                        <tr>
                                            <td>
                                                {{ $webhook->name }} <br>
                                                <span class="badge bg-primary rounded-pill">Sincronização: {{ $webhook->webhookAssas()['interrupted'] == 1 ? 'Inativa' : 'Ativa' }}</span>
                                                <span class="badge bg-primary rounded-pill">Ativo: {{ $webhook->webhookAssas()['enabled'] == 1 ? 'Sim' : 'Não' }}</span>
                                            </td>
                                            <td>
                                                <a onclick="onClip('{{ $webhook->url }}')" class="badge bg-dark">URL de sincronização</a>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('updated-webhook') }}" method="POST" class="delete">
                                                    @csrf
                                                    <input type="hidden" name="uuid" value="{{ $webhook->uuid }}"> 
                                                    <div class="btn-group" role="group">
                                                        @if ($webhook->webhookAssas()['interrupted'] == true || $webhook->webhookAssas()['enabled'] == false)
                                                            <button type="submit" class="btn btn-dark btn-sm">Ativar</button>
                                                        @endif
                                                    </div>
                                                </form>
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
</section>
@endsection