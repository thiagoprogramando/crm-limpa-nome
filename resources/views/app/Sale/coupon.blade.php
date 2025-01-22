@extends('app.layout')
@section('title') Gestão de Cupons @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Cupons</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Cupons</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            <div class="btn-group mb-3" role="group">
                @if (Auth::user()->type == 1)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Cadastrar</button>
                @endif
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                <button type="button" id="gerarExcel" class="btn btn-outline-primary">Excel</button>
            </div>

            <div class="modal fade" id="registerModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('create-coupon') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Detalhes:</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o Nome:">
                                            <label for="floatingName">Código:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="description" class="form-control" id="floatingDescription" placeholder="Descrição:">
                                            <label for="floatingDescription">Descrição:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="expiry_date" class="form-control" id="floatingExpiry_date" placeholder="Data de Expiração:">
                                            <label for="floatingExpiry_date">Data de Expiração:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <select name="id_user" class="form-select" id="floatingSeller">
                                                <option selected="" value="">Usuário:</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>  
                                                @endforeach
                                            </select>
                                            <label for="floatingSeller">Usuários</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="number" name="percentage" class="form-control" id="floatingPercentage" placeholder="Porcentagem (%):">
                                            <label for="floatingPercentage">Porcentagem (%):</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="number" name="qtd" class="form-control" id="floatingQtd" placeholder="Quantidade:">
                                            <label for="floatingQtd">Quantidade:</label>
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
                        <form action="{{ route('coupons') }}" method="GET">
                            <div class="modal-header">
                                <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-12 mb-1">
                                        <div class="form-floating">
                                            <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o Nome:">
                                            <label for="floatingName">Código:</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 mb-1">
                                        <div class="form-floating">
                                            <input type="date" name="expiry_date" class="form-control" id="floatingExpiry_date" placeholder="Data de Expiração:">
                                            <label for="floatingExpiry_date">Data de Expiração:</label>
                                        </div>
                                    </div>
                                    @if (Auth::user()->type == 1)
                                        <div class="col-12 col-md-6 col-lg-6 mb-1">
                                            <div class="form-floating">
                                                <select name="id_user" class="form-select" id="floatingSeller">
                                                    <option selected="" value="">Usuário:</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>  
                                                    @endforeach
                                                </select>
                                                <label for="floatingSeller">Usuários</label>
                                            </div>
                                        </div>
                                    @endif
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

            <div class="card p-2">
                <div class="card-body">
                    <h5 class="card-title">Cupons</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">N°</th>
                                    <th scope="col">Código</th>
                                    <th scope="col">Descrição</th>
                                    <th class="text-center" scope="col">Desconto (%)</th>
                                    <th class="text-center" scope="col">Qtd</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($coupons as $coupon)
                                    <tr>
                                        <th scope="row">{{ $coupon->id }}</th>
                                        <td>
                                            {{ $coupon->name }} <br>
                                            <span class="badge bg-dark">Usuário: @if($coupon->user) {{ $coupon->user->name }} @else Sem associação @endif</span>
                                        </th>
                                        <td>
                                            {{ substr($coupon->description, 0, 40) }} <br>
                                            <span class="badge bg-primary">Expiração: @if($coupon->expiry_date) {{ \Carbon\Carbon::parse($coupon->expiry_date)->format('d/m/Y') }} @else --- @endif</span>
                                        </td>
                                        <td class="text-center">{{ number_format($coupon->percentage, 2, ',', '.') }}%</td>
                                        <td class="text-center">{{ $coupon->qtd }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-coupon') }}" method="POST" class="delete">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $coupon->id }}"> 
                                                <div class="btn-group" role="group" aria-label="Basic outlined example">
                                                    <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        {{ $coupons->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function confirmDelete() {
        Swal.fire({
            title: 'Você tem certeza?',
            text: "A exclusão do CUPOM impossibilita a utilização!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, deletar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('delete-coupon') }}";
            }
        });
    }
</script>
@endsection