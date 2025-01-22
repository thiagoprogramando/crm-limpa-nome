@extends('app.layout')
@section('title') Lançamento de venda: {{ $product->name }} @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Lançamento de venda: {{ $product->name }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Lançamento de venda</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-2">

                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-justified" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Enviar Nome</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sale-tab" data-bs-toggle="tab" data-bs-target="#sale-justified" type="button" role="tab" aria-controls="sale" aria-selected="true">Nomes Enviados</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-justified" type="button" role="tab" aria-controls="home" aria-selected="true">Saiba Mais</button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="myTabjustifiedContent">
                            <div class="tab-pane fade active show" id="profile-justified" role="tabpanel" aria-labelledby="profile-tab">

                                <div class="btn-group mt-2 mb-3" role="group">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nameModal">Adicionar Nome</button>
                                    <button type="button" class="btn btn-outline-primary">Nomes: {{ $sales->count() }}</button>
                                    <button type="button" class="btn btn-outline-primary">Valor Total: R$ {{ $sales->count() * Auth::user()->fixed_cost }}</button>
                                </div>

                                <form action="{{ route('create-upload') }}" method="POST" class="row">
                                    <div class="modal fade" id="nameModal" tabindex="-1" style="display: none;" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Enviar Nome (Associação)</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body row">
                                                    @csrf
                                                    <input type="hidden" name="product" value="{{ $product->id }}">
                                                    <input type="hidden" name="id_seller" value="{{ Auth::user()->id }}">
                                                    <input type="hidden" name="value" value="{{ Auth::user()->fixed_cost }}">
                                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                        <div class="form-floating mb-2">
                                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o nome do Cliente:" required>
                                                            <label for="floatingName">Nome:</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                                                        <div class="form-floating mb-2">
                                                            <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCnpj" placeholder="Informe o CPF ou CNPJ do Cliente:" oninput="mascaraCpfCnpj(this)" required>
                                                            <label for="floatingCpfCnpj">CPF ou CNPJ:</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                                                        <div class="form-floating mb-2">
                                                            <input type="text" name="birth_date" class="form-control" id="floatingBirth_date" placeholder="Data Nascimento:" oninput="mascaraData(this)">
                                                            <label for="floatingBirth_date">Data Nascimento:</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer btn-group">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                                    <button type="submit" class="btn btn-primary">Enviar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table table-sm" id="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Lista</th>
                                                <th scope="col">Nome</th>
                                                <th scope="col">CPF/CNPJ</th>
                                                <th scope="col" class="text-center">Status Pagamento</th>
                                                <th scope="col" class="text-center">Opções</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sales as $sale)
                                                <tr>
                                                    <th scope="row">{{ $sale->id }}</th>
                                                    <td>{{ $sale->list->name }}</td>
                                                    <td>{{ $sale->user->name }}</td>
                                                    <td>{{ $sale->user->cpfcnpj }}</td>
                                                    <td class="text-center">
                                                        {{ $sale->statusLabel() }} <br>
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="{{ route('delete-sale') }}" method="POST" class="delete">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $sale->id }}"> 
                                                            <div class="btn-group" role="group">
                                                                <button type="submit" class="btn btn-danger text-light" title="Excluir"><i class="bi bi-trash"></i></button>
                                                                @if ($sale->token_payment)
                                                                    <a href="{{ route('update-sale', ['id' => $sale->id]) }}" class="btn btn-success text-light" title="Pagar Nome"><i class="bi bi-upc"></i> Acessar Fatura</a>
                                                                @else
                                                                    <a href="{{ route('create-payment-upload', ['id' => $sale->id]) }}" class="btn btn-primary text-light" title="Pagar Nome"><i class="bi bi-upc"></i> Pagar</a>
                                                                @endif
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="sale-justified" role="tabpanel" aria-labelledby="sale-tab">
                                <div class="table-responsive p-3">
                                    <table class="table table-sm" id="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Lista</th>
                                                <th scope="col">Nome</th>
                                                <th scope="col">CPF/CNPJ</th>
                                                <th scope="col" class="text-center">Status Pagamento</th>
                                                <th scope="col" class="text-center">Opções</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($salesApproved as $approveds)
                                                <tr>
                                                    <th scope="row">{{ $approveds->id }}</th>
                                                    <td>{{ $approveds->list->name }}</td>
                                                    <td>{{ $approveds->user->name }}</td>
                                                    <td>{{ $approveds->user->cpfcnpj }}</td>
                                                    <td class="text-center">
                                                        {{ $approveds->statusLabel() }} <br>
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="{{ route('delete-sale') }}" method="POST" class="delete">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $approveds->id }}"> 
                                                            <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                                <a title="Faturas" href="{{ route('update-sale', ['id' => $approveds->id]) }}" class="btn btn-outline-primary"><i class="bi bi-currency-dollar"></i></a>
                                                                @if ($approveds->status == 1 && (Auth::user()->type == 1 || Auth::user()->level == 4 || Auth::user()->level == 5))
                                                                    <a title="Reprotocolar" href="{{ route('reprotocol-sale', ['id' => $approveds->id]) }}" class="btn btn-outline-primary"><i class="bx bx-check-shield"></i></a>
                                                                @endif
                                                                <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="home-justified" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row p-3">
                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 text-center">
                                        <iframe class="embed-responsive-item w-100 h-100" src="https://www.youtube.com/embed/mM9CuIP2dWo?si=Yz1QIOagkGaHNHfd" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                                        <p class="card-title">Como Enviar Nomes Diretamente para a Associação sem Usar Planilhas do Excel?</p>
                                        <p>
                                            Ao escolher enviar os nomes diretamente para a associação, 
                                            <b>o cliente não receberá um link de pagamento ou contrato, pois os dados serão encaminhados diretamente para a associação.</b> 
                                            Nesse caso, você precisará apenas pagar o valor de custo, sem a necessidade de enviar a ficha associativa ou documentos adicionais.
                                            <br><br>
                                            <b class="text-danger">Atenção:</b> É possível gerar boletos extras ou adicionais, porém os dados do <a href="{{ route('list-client') }}">cliente</a> devem estar completos.
                                        </p>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection