@extends('app.layout')
@section('title') Gestão de Rede @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Gestão de Rede</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Gestão de Rede</li>
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

                <div class="modal fade" id="createModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('created-user') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="2">
                                <input type="hidden" name="associtiation_id" value="{{ Auth::user()->associtiation_id }}">
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

                <div class="modal fade" id="filterModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('list-network') }}" method="GET">
                                <div class="modal-header">
                                    <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-7 col-lg-7 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Nome:">
                                                <label for="floatingName">Nome:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5 col-lg-5 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="created_at" class="form-control" id="floatingCreated_at" placeholder="Informe a data:">
                                                <label for="floatingCreated_at">Data de cadastro:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-7 col-lg-7 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="email" class="form-control" id="floatingEmail" placeholder="Email:">
                                                <label for="floatingEmail">Email:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5 col-lg-5 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="cpfcnpj" class="form-control" id="floatingCpfCNpj" placeholder="CPF/CNPJ:">
                                                <label for="floatingCpfCNpj">CPF/CNPJ:</label>
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

                <div class="card">
                    <div class="card-body m-0 p-0">
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
                                                        <div class="row align-items-start">
                                                            <input type="hidden" name="id" value="{{ $user->id }}">
                                                            <div class="col-12 col-sm-12 col-md-8 col-lg-8 row">
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
                                                                        <input type="text" name="phone" class="form-control phone" id="floatingPhone" placeholder="Whatsapp:" oninput="mascaraTelefone(this)" value="{{ $user->phone }}">
                                                                        <label for="floatingPhone">Whatsapp:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="cpfcnpj" class="form-control cpfcnpj" id="floatingCpfCnpj" placeholder="CPF/CNPJ:" oninput="mascaraCpfCnpj(this)" value="{{ $user->cpfcnpj }}">
                                                                        <label for="floatingCpfCnpj">CPF/CNPJ:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                    <div class="form-floating">
                                                                        <input type="date" name="birth_date" class="form-control" id="floatingBirthDate" placeholder="Data Nascimento:" value="{{ $user->birth_date }}">
                                                                        <label for="floatingBirthDate">Data Nascimento:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 offset-md-6 col-md-6 offset-lg-6 col-lg-6 mb-2 d-grid gap-2">
                                                                    <div class="btn-group">
                                                                        <button type="button" id="deleteUserBtn" class="btn btn-sm btn-outline-danger">Excluir </button>
                                                                        <button type="submit" id="updateUserBtn" class="btn btn-sm btn-outline-primary">Atualizar </button>
                                                                        <a href="https://wa.me/{{ $user->phone }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-whatsapp"></i></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                           
                                                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 row">
                                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control money" name="fixed_cost" id="fixed_cost" placeholder="Custo (Mín: {{ Auth::user()->fixed_cost }}):" value="{{ $user->fixed_cost }}" oninput="maskValue(this)">
                                                                        <label for="fixed_cost">Custo (Mín: {{ Auth::user()->fixed_cost }}):</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="faturamentoTotal" placeholder="Faturamente Total:" value="R$ {{ number_format($user->invoices->where('status', 1)->sum('value'), 2, ',', '.') }}" disabled>
                                                                        <label for="faturamentoTotal">Faturamente Total:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                    <div class="form-floating">
                                                                        <input type="text" class="form-control" id="ComissãoTotal" placeholder="Comissão Total:" value="R$ {{ number_format($user->invoices->where('status', 1)->sum('commission_seller'), 2, ',', '.') }}" disabled>
                                                                        <label for="ComissãoTotal">Comissão Total:</label>
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
                                                                    <th scope="col">Valor</th>
                                                                    <th scope="col">Vencimento</th>
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
                                                                        <td>R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                                                        <td class="text-center">
                                                                            <div class="btn-group">
                                                                                <a href="{{ $invoice->url_payment }}" target="_blank" class="btn btn-sm btn-outline-primary"> Acessar </a>
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
    </section>

    <script>
        document.getElementById('copyUrlBtn').addEventListener('click', function() {
            const minCost = {{ Auth::user()->fixed_cost }};
            Swal.fire({
                title: 'Informe o custo',
                input: 'number',
                inputLabel: `Custo mínimo: R$ ${minCost.toFixed(2)}`,
                inputPlaceholder: minCost,
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Por favor, insira um valor!';
                    } else if (parseFloat(value) < minCost) {
                        return `O valor mínimo é R$ ${minCost.toFixed(2)}.`;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const value = parseFloat(result.value).toFixed(2);
                    const url = `{{ route('registrer', ['id' => Auth::id()]) }}/${value}`;
                    navigator.clipboard.writeText(url).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'URL copiada!',
                            text: 'A URL foi copiada para a área de transferência.',
                            confirmButtonText: 'OK'
                        });
                    }).catch((err) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Houve um erro ao copiar a URL: ' + err,
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        });
    </script>
@endsection