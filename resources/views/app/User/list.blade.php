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
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="created_at_start" class="form-control" id="floatingCreated_atStart" placeholder="Data inicial:">
                                            <label for="floatingCreated_atStart">Data inicial:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
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
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Pessoas</h5>
                            
                            <div class="accordion" id="accordionExample">
                                @foreach ($users as $user)
                                
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $user->id }}" aria-expanded="false" aria-controls="collapse{{ $user->id }}">
                                                #{{ $user->id }} - {{ $user->name }}
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
                                                        <form action="{{ route('update-user') }}" method="POST" id="userForm">
                                                            @csrf
                                                            <div class="row">
                                                                <input type="hidden" name="id" value="{{ $user->id }}">
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="floatingParent" placeholder="Patrocinador:" value="{{ $user->parent->name ?? '' }}" disabled readonly>
                                                                        <label for="floatingParent">Patrocinador:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:" value="{{ $user->name }}">
                                                                        <label for="floatingName">Nome:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="floatingBalance" placeholder="Saldo Diponível:" value="{{ $user->balance() }}" disabled readonly>
                                                                        <label for="floatingBalance">Saldo Diponível:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email:" value="{{ $user->email }}">
                                                                        <label for="floatingEmail">Email:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="phone" class="form-control" id="floatingPhone" placeholder="Whatsapp:" oninput="mascaraTelefone(this)" value="{{ $user->phone }}">
                                                                        <label for="floatingPhone">Whatsapp:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCnpj" placeholder="CPF/CNPJ:" oninput="mascaraCpfCnpj(this)" value="{{ $user->cpfcnpj }}">
                                                                        <label for="floatingCpfCnpj">CPF/CNPJ:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="date" name="birth_date" class="form-control" id="floatingBirthDate" placeholder="Data Nascimento:" value="{{ $user->birth_date }}">
                                                                        <label for="floatingBirthDate">Data Nascimento:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <select name="type" class="form-select" id="floatingType">
                                                                            <option selected value="{{ $user->type }}">Tipos:</option>
                                                                            <option value="1" @selected($user->type == 1)>Administrador</option>
                                                                            <option value="2" @selected($user->type == 2)>Consultor</option>
                                                                            <option value="3" @selected($user->type == 3)>Cliente</option>
                                                                            <option value="4" @selected($user->type == 4)>Vendendor Interno</option>
                                                                        </select>
                                                                        <label for="floatingType">Tipo</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
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
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <select name="white_label_contract" class="form-select" id="floatingWlContract">
                                                                            <option selected @selected($user->white_label_contract == 0) value="">Opções:</option>
                                                                            <option value="1" @selected($user->white_label_contract == 1)>Liberado</option>
                                                                            <option value="2" @selected($user->white_label_contract == 2)>Bloqueado</option>
                                                                        </select>
                                                                        <label for="floatingWlContract">White Label Contrato</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="fixed_cost" class="form-control" id="fixed_cost" placeholder="Custo Fixo (R$):" value="{{ $user->fixed_cost }}" oninput="mascaraReal(this)">
                                                                        <label for="fixed_cost">Custo Fixo (R$):</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-7 col-lg-7 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="wallet" class="form-control" id="wallet" placeholder="Wallet:" value="{{ $user->wallet }}">
                                                                        <label for="wallet">Wallet:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="password" class="form-control" id="password" placeholder="Senha:">
                                                                        <label for="password">Senha:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="api_key" class="form-control" id="api_key" placeholder="Api Token ZapSing:" value="{{ $user->api_key }}">
                                                                        <label for="api_key">Api Key:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-1">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="api_token_zapapi" class="form-control" id="api_token_zapapi" placeholder="Api Token ZapSing:" value="{{ $user->api_token_zapapi }}">
                                                                        <label for="api_token_zapapi">Token ZapApi:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 offset-md-6 col-md-6 offset-lg-6 col-lg-6 mb-1 btn-group">
                                                                    <a href="https://wa.me/{{ $user->phone }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-whatsapp"></i></a>
                                                                    <button type="button" id="deleteUserBtn" class="btn btn-outline-dark">
                                                                        <i class="bi bi-trash"></i> Excluir
                                                                    </button>
                                                                    <button type="submit" id="updateUserBtn" class="btn btn-primary">
                                                                        <i class="bi bi-check"></i> Atualizar informações
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="tab-pane fade" id="profile{{ $user->id }}" role="tabpanel" aria-labelledby="profile-tab{{ $user->id }}">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">#</th>
                                                                        <th scope="col">Fatura</th>
                                                                        <th scope="col">Valor</th>
                                                                        <th scope="col">Vencimento</th>
                                                                        <th scope="col" class="text-center">Opções</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($user->invoices as $invoice)
                                                                        <tr>
                                                                            <th scope="row">
                                                                                {{ $invoice->id }} <br>
                                                                                <span class="badge {{ $invoice->status == 1 ? 'bg-success' : 'bg-dark' }}">{{ $invoice->statusLabel() }}</span>
                                                                            </th>
                                                                            <td>
                                                                                {{ $invoice->name }} <br>
                                                                                <span class="badge bg-dark">{{ $invoice->description }}</span>
                                                                            </td>
                                                                            <td>R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                                                            <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                                                            <td class="text-center">
                                                                                <div class="btn-group">
                                                                                    @if(!empty($user->wallet))
                                                                                        <a href="{{ route('payMonthly', ['id' => $invoice->id]) }}" class="btn btn-success text-light">
                                                                                            <i class="bi bi-credit-card"></i> Pagar com saldo
                                                                                        </a>
                                                                                    @endif
                                                                                    <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-primary text-light">
                                                                                        <i class="bi bi-arrow-up-right-circle"></i> Acessar Fatura
                                                                                    </a>
                                                                                    @if($invoice->status <> 1 )
                                                                                        <a href="{{ route('delete-invoice', ['id' => $invoice->id]) }}" class="btn btn-danger text-light confirm">
                                                                                            <i class="bi bi-trash"></i>
                                                                                        </a>
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

                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Crescimento {{ date('Y') }}</h5>
                            <div id="lineChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{ asset('assets/dashboard/vendor/apexcharts/apexcharts.min.js') }}"></script>
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

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('userForm');
        const deleteBtn = document.getElementById('deleteUserBtn');
        const updateBtn = document.getElementById('updateUserBtn');

        deleteBtn.addEventListener('click', function () {
            Swal.fire({
                title: 'Tem certeza?',
                text: 'Você realmente deseja excluir este registro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                confirmButtonColor: '#008000',
                cancelButtonText: 'Não',
                cancelButtonColor: '#FF0000',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = "{{ route('delete-user') }}";
                    form.submit();
                }
            });
        });

        updateBtn.addEventListener('click', function () {
            form.action = "{{ route('update-user') }}";
        });

        const usersData = @json($usersData);
        new ApexCharts(document.querySelector("#lineChart"), {
            series: [{
                name: "Usuários",
                data: usersData
            }],
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            }
        }).render();
    });
</script>
@endsection