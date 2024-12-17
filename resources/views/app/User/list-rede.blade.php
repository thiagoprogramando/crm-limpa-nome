@extends('app.layout')
@section('title') Gestão de Rede @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Gestão de Rede</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Gestão de Rede</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                    <a href="javascript:void(0)" id="copyUrlBtn" class="btn btn-outline-primary">Cadastrar</a>
                    <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
                </div>

                <div class="modal fade" id="filterModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('list-rede') }}" method="GET">
                                <div class="modal-header">
                                    <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:">
                                                <label for="floatingName">Nome:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                                <label for="floatingCreated_at">Data de cadastro:</label>
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

                <div class="card p-2">
                    <div class="card-body">
                        <h5 class="card-title">Minha Rede</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th scope="col">N°</th>
                                        <th scope="col">Nome</th>
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
                                            <td class="text-center">R$ {{ number_format($user->saleTotal(), 2, ',', '.') }}</td>
                                            <td class="text-center">R$ {{ number_format($user->commissionTotal(), 2, ',', '.') }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('delete-user') }}" method="POST" class="delete btn-group">
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
                                                                        <input type="text" name="api_token_zapapi" class="form-control" id="api_token_zapapi" placeholder="Api Token ZapSing:" value="{{ $user->api_token_zapapi }}">
                                                                        <label for="api_token_zapapi">Api Token ZapSing:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="fixed_cost" class="form-control" id="fixed_cost" placeholder="Custo Fixo (R$):" oninput="mascaraReal(this)" value="{{ $user->fixed_cost }}">
                                                                        <label for="fixed_cost">Custo (Min R$ {{Auth::user()->fixed_cost}}):</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer btn-group">
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
        document.getElementById('copyUrlBtn').addEventListener('click', function() {
            const url = "{{ route('registrer', ['id' => Auth::id()]) }}";
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'URL copiada!',
                    text: 'A URL foi copiada para a área de transferência.',
                    confirmButtonText: 'OK'
                });
            }).catch(function(err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Houve um erro ao copiar a URL: ' + err,
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
@endsection