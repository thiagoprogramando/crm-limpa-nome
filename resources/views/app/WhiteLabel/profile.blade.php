@extends('app.layout')
@section('title') Detalhes do Contrato @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Detalhes do Contrato</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Detalhes do Contrato</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row p-3">
                        <div class="col-5">
                            <p class="lead mb-0"><b>Como funciona?</b></p>
                            <p>
                                No formulário ao lado, você pode preencher os dados que deseja que apareçam como <b>CONTRATADA</b> 
                                nos contratos gerados para você e sua <a href="{{ route('list-network') }}"><b>REDE</b></a>.
                                Esses dados serão utilizados automaticamente para preencher as informações de contrato, 
                                garantindo que o processo de criação de contratos seja rápido e eficiente.
                            </p>
                        </div>
                        <div class="col-7">
                            <form action="{{ route('update-user') }}" method="POST" class="row g-3">
                                @csrf
                                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
        
                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="company_name" value="{{ Auth::user()->company_name }}" class="form-control" id="floatingName" placeholder="Nome Fantasia:">
                                        <label for="floatingName">Nome Fantasia:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="company_cpfcnpj" value="{{ Auth::user()->company_cpfcnpj }}" class="form-control" id="floatingCpfcnpj" placeholder="CNPJ:">
                                        <label for="floatingCpfcnpj">CNPJ:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                    <div class="form-floating">
                                        <input type="text" name="company_address" value="{{ Auth::user()->company_address }}" class="form-control" id="floatingAddress" placeholder="Endereço:">
                                        <label for="floatingAddress">Endereço:</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                    <div class="form-floating">
                                        <input type="email" name="company_email" value="{{ Auth::user()->company_email }}" class="form-control" id="floatingEmail" placeholder="Email:">
                                        <label for="floatingEmail">Email:</label>
                                    </div>
                                </div>
                                <div class="col-12 offset-md-7 col-md-5 offset-lg-7 col-lg-5 d-grid gap-2">
                                    <button type="submit" class="btn btn-outline-success rounded-pill" type="button">Atualizar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection