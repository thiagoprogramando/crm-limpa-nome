@extends('app.layout')
@section('title') Gestão de Pessoas @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Pessoas</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Pessoas</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                <a href="{{ route('registrer') }}" id="copy-url-btn" class="btn btn-outline-primary">Cadastrar</a>
                <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
            </div>

            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('listuser', ['type' => $type]) }}" method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:">
                                            <label for="floatingName">Nome:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                            <label for="floatingCreated_at">Data:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="value" class="form-control" id="floatingValue" placeholder="Informe o valor:" oninput="mascaraReal(this)">
                                            <label for="floatingValue">Valor:</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-success">Consultar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="card-body">
                    <h5 class="card-title">Pessoas</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">N°</th>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Graduação</th>
                                    <th scope="col">Situação</th>
                                    <th class="text-center" scope="col">Mens. Abertas</th>
                                    <th class="text-center" scope="col">T. Vendas</th>
                                    <th class="text-center" scope="col">T. Comissão</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <th scope="row">{{ $user->id }}</th>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->levelLabel() }}</td>
                                        <td>{{ $user->statusLabel() }}</td>
                                        <td class="text-center">{{ $user->invoicesPendent() }}</td>
                                        <td class="text-center">R$ {{ number_format($user->saleTotal(), 2, ',', '.') }}</td>
                                        <td class="text-center">R$ {{ number_format($user->commissionTotal(), 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-user') }}" method="POST" class="delete">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $user->id }}">
                                                <button type="button" class="btn btn-warning text-light" data-bs-toggle="modal" data-bs-target="#updateModal{{ $user->id }}"><i class="bi bi-arrow-up-right-circle"></i></button>
                                                <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="updateModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('update-user') }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar dados</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">

                                                            <input type="hidden" name="id" value="{{ $user->id }}">
                                                            
                                                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                <div class="form-floating">
                                                                    <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:" value="{{ $user->name }}">
                                                                    <label for="floatingName">Nome:</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                <div class="form-floating">
                                                                    <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email:" value="{{ $user->email }}">
                                                                    <label for="floatingEmail">Email:</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                                                <div class="form-floating">
                                                                    <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCnpj" placeholder="CPF/CNPJ:" oninput="mascaraCpfCnpj(this)" value="{{ $user->cpfcnpj }}">
                                                                    <label for="floatingCpfCnpj">CPF/CNPJ:</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 mb-1">
                                                                <div class="form-floating">
                                                                    <input type="date" name="birth_date" class="form-control" id="floatingBirthDate" placeholder="Data Nascimento:" value="{{ $user->birth_date }}">
                                                                    <label for="floatingBirthDate">Data Nascimento:</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                <div class="form-floating">
                                                                    <select name="type" class="form-select" id="floatingType">
                                                                        <option selected value="{{ $user->type }}">Tipo:</option>
                                                                        <option value="1">Administrador</option>
                                                                        <option value="2">Consultor</option>
                                                                        <option value="5">Consultor (Indicador)</option>
                                                                        <option value="7">Consultor (Master)</option>
                                                                        <option value="3">Cliente</option>
                                                                        <option value="4">Vendendor Interno</option>
                                                                        <option value="8">Vendendor Master</option>
                                                                    </select>
                                                                    <label for="floatingType">Tipo</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                <div class="form-floating">
                                                                    <select name="level" class="form-select" id="floatingLevel">
                                                                        <option selected value="{{ $user->type }}">Graduação:</option>
                                                                        <option value="1">INICIANTE</option>
                                                                        <option value="2">CONSULTOR</option>
                                                                        <option value="3">CONSULTOR LÍDER</option>
                                                                        <option value="7">CONSULTOR MASTER</option>
                                                                        <option value="4">REGIONAL</option>
                                                                        <option value="5">GERENTE REGIONAL</option>
                                                                        <option value="6">VENDEDOR INTERNO</option>
                                                                        <option value="8">VENDEDOR MASTER</option>
                                                                    </select>
                                                                    <label for="floatingLevel">Graduação</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                <div class="form-floating">
                                                                    <input type="text" name="api_token_zapapi" class="form-control" id="api_token_zapapi" placeholder="Api Token ZapSing:" value="{{ $user->api_token_zapapi }}">
                                                                    <label for="api_token_zapapi">Api Token ZapSing:</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                                        <button type="submit" class="btn btn-success">Atualizar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.getElementById('copy-url-btn').addEventListener('click', function(event) {

        event.preventDefault(); 
        const tempInput = document.createElement('input');
        tempInput.value = this.href;
        document.body.appendChild(tempInput);

        tempInput.select();
        document.execCommand('copy');

        document.body.removeChild(tempInput);

        Swal.fire({
            title: 'Sucesso!',
            text: 'Copiado para área de trabalho.',
            icon: 'success',
            timer: 2000
        });
    });
</script>

@endsection