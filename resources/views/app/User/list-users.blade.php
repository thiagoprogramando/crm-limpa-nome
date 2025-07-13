@extends('app.layout')
@section('title') Gestão de Pessoas @endsection
@section('conteudo')

    <div class="pagetitle">
        <h1>Gestão de Pessoas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Gestão de Pessoas</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createModal">Cadastrar</button>
                </div>

                <div class="modal fade" id="filterModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('list-user', ['type' => $type]) }}" method="GET">
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
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="email" class="form-control" id="floatingEmail" placeholder="Email:">
                                                <label for="floatingEmail">Email:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCNpj" placeholder="CPF/CNPJ:">
                                                <label for="floatingCpfCNpj">CPF/CNPJ:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="created_at_start" class="form-control" id="floatingCreated_atStart" placeholder="Data inicial:">
                                                <label for="floatingCreated_atStart">Data inicial:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="created_at_end" class="form-control" id="floatingCreated_atEnd" placeholder="Data final:">
                                                <label for="floatingCreated_atEnd">Data final:</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer btn-group">
                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-primary">Consultar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="createModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('created-user', ['type' => $type]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Dados do Cadastro</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="name" placeholder="Nome:" required>
                                                <label for="name">Nome:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-7 col-lg-7 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="email" class="form-control" id="email" placeholder="Email:" required>
                                                <label for="email">Email:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5 col-lg-5 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="cpfcnpj" class="form-control" id="cpfcnpj" placeholder="CPF/CNPJ:" oninput="maskCpfCnpj(this)" required>
                                                <label for="cpfcnpj">CPF/CNPJ:</label>
                                            </div>
                                        </div>
                                        @if ($type == 99)
                                            <div class="col-12 col-md-7 col-lg-7 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="association_id" class="form-control" id="association_id" placeholder="Código Associação:">
                                                    <label for="association_id">Código Associação:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-5 col-lg-5 mb-2 d-grid">
                                                <button type="button" onclick="newCode()" class="btn btn-outline-primary">Gerar</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer btn-group">
                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-body m-0 p-0">
                                <div class="accordion" id="accordionExample">
                                    @foreach ($users as $user)
                                    
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $user->id }}" aria-expanded="false" aria-controls="collapse{{ $user->id }}">
                                                    {{ $user->name }}
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $user->id }}" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample" style="">
                                                <div class="accordion-body">
                                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <button class="nav-link active" id="home-tab{{ $user->id }}" data-bs-toggle="tab" data-bs-target="#home{{ $user->id }}" type="button" role="tab" aria-controls="home" aria-selected="true"><i class="bi bi-person-lines-fill"></i> Dados</button>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <button class="nav-link" id="profile-tab{{ $user->id }}" data-bs-toggle="tab" data-bs-target="#profile{{ $user->id }}" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1"><i class="bi bi-bank"></i> Faturas</button>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content pt-2" id="myTabContent">
                                                        <div class="tab-pane fade show active" id="home{{ $user->id }}" role="tabpanel" aria-labelledby="home-tab{{ $user->id }}">
                                                            <form action="{{ route('update-user') }}" method="POST">
                                                                @csrf
                                                                <div class="row align-items-start">
                                                                    <input type="hidden" name="id" value="{{ $user->id }}">

                                                                    <div class="col-12 col-md-8 col-lg-7 row">
                                                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="text" class="form-control" id="floatingParent" placeholder="Patrocinador:" value="{{ $user->sponsor->name ?? '' }}" disabled readonly>
                                                                                <label for="floatingParent">Patrocinador:</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:" value="{{ $user->name }}">
                                                                                <label for="floatingName">Nome:</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email:" value="{{ $user->email }}">
                                                                                <label for="floatingEmail">Email:</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="text" name="phone" class="form-control phone" id="floatingPhone" placeholder="Whatsapp:" oninput="maskPhone(this)" value="{{ $user->phone }}">
                                                                                <label for="floatingPhone">Whatsapp:</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="text" name="cpfcnpj" class="form-control cpfcnpj" id="floatingCpfCnpj" placeholder="CPF/CNPJ:" oninput="maskCpfCnpj(this)" value="{{ $user->cpfcnpj }}">
                                                                                <label for="floatingCpfCnpj">CPF/CNPJ:</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="date" name="birth_date" class="form-control" id="floatingBirthDate" placeholder="Data Nascimento:" value="{{ $user->birth_date }}">
                                                                                <label for="floatingBirthDate">Data Nascimento:</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-12 col-md-4 col-lg-5 row">
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="text" class="form-control" id="floatingBalance" placeholder="Saldo Diponível:" value="{{ $user->balance() }}" disabled readonly>
                                                                                <label for="floatingBalance">Saldo Diponível:</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <input type="text" name="fixed_cost" class="form-control" id="fixed_cost" placeholder="Custo Fixo (R$):" value="{{ $user->fixed_cost }}" oninput="maskValue(this)">
                                                                                <label for="fixed_cost">Custo Fixo (R$):</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <select name="type" class="form-select" id="floatingType">
                                                                                    <option selected value="{{ $user->type }}">Tipos:</option>
                                                                                    <option value="1" @selected($user->type == 1)>Administrador</option>
                                                                                    <option value="2" @selected($user->type == 2)>Consultor</option>
                                                                                    <option value="3" @selected($user->type == 3)>Cliente</option>
                                                                                    <option value="4" @selected($user->type == 4)>Vendendor Interno</option>
                                                                                    <option value="99" @selected($user->type == 99)>Sócio</option>
                                                                                </select>
                                                                                <label for="floatingType">Tipo</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                            <div class="form-floating">
                                                                                <select name="level" class="form-select" id="floatingLevel">
                                                                                    <option selected value="{{ $user->level }}">Graduações:</option>
                                                                                    <option value="1" @selected($user->level == 1)>Iniciante</option>
                                                                                    <option value="2" @selected($user->level == 2)>Consultor</option>
                                                                                    <option value="3" @selected($user->level == 3)>Consultor Líder</option>
                                                                                    <option value="4" @selected($user->level == 4)>Regional</option>
                                                                                    <option value="5" @selected($user->level == 5)>Gerente Regional</option>
                                                                                    <option value="7" @selected($user->level == 7)>Diretor</option>
                                                                                    <option value="8" @selected($user->level == 8)>Diretor Vip</option>
                                                                                    <option value="9" @selected($user->level == 9)>Presidente Vip</option>
                                                                                    <option value="6" @selected($user->level == 7)>Vendedor Interno</option>
                                                                                </select>
                                                                                <label for="floatingLevel">Graduação</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12 col-md-12 col-lg-12 mb-2 mb-2 d-grid">
                                                                            <div class="btn-group">
                                                                                <a href="https://wa.me/{{ $user->phone }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-whatsapp"></i></a>
                                                                                <button type="submit" class="btn btn-sm btn-primary"> Salvar </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="tab-pane fade" id="profile{{ $user->id }}" role="tabpanel" aria-labelledby="profile-tab{{ $user->id }}">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">Fatura</th>
                                                                            <th scope="col" class="text-center">Valor</th>
                                                                            <th scope="col" class="text-center">Vencimento</th>
                                                                            <th scope="col" class="text-center">Opções</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($user->invoices as $invoice)
                                                                            <tr>
                                                                                <td>
                                                                                    {{ $invoice->name }} <br>
                                                                                    <span class="badge {{ $invoice->status == 1 ? 'bg-success' : 'bg-primary' }}">{{ $invoice->statusLabel() }}</span>
                                                                                </td>
                                                                                <td class="text-center">R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                                                                <td class="text-center">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                                                                <td class="text-center">
                                                                                    <div class="btn-group">
                                                                                        <a href="{{ $invoice->payment_url }}" target="_blank" class="btn btn-sm btn-outline-primary"> Acessar </a>
                                                                                        @if($invoice->status <> 1 )
                                                                                            <a href="" class="btn btn-sm btn-outline-danger confirm"> Excluir </a>
                                                                                        @endif
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                        
                                <div class="text-center">
                                    {{ $users->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function newCode() {
            const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const numbers = '0123456789';
            let code = '';

            for (let i = 0; i < 6; i++) {
                code += letters.charAt(Math.floor(Math.random() * letters.length));
                code += numbers.charAt(Math.floor(Math.random() * numbers.length));
            }

            document.getElementById('association_id').value = code;
        }
    </script>
@endsection