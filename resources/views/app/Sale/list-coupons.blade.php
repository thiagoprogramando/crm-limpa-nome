@extends('app.layout')
@section('title') Gestão de Cupons @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Gestão de Cupons</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Gestão de Cupons</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">

                <div class="btn-group mb-3" role="group">
                    @if (Auth::user()->type == 1)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#registerModal">Cadastrar</button>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtros</button>
                </div>

                <div class="modal fade" id="registerModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('created-coupon') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalhes:</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-7 col-md-8 col-lg-8 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o Nome:">
                                                <label for="floatingName">Código:</label>
                                            </div>
                                        </div>
                                        <div class="col-5 col-md-4 col-lg-4 mb-2">
                                            <div class="form-floating">
                                                <input type="number" name="qtd" class="form-control" id="floatingQtd" placeholder="Quantidade:">
                                                <label for="floatingQtd">Quantidade:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="description" class="form-control" id="floatingDescription" placeholder="Descrição:">
                                                <label for="floatingDescription">Descrição:</label>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="expiry_date" class="form-control" id="floatingExpiry_date" placeholder="Data de Expiração:">
                                                <label for="floatingExpiry_date">Data de Expiração:</label>
                                            </div>
                                        </div>
                                        <div class="col-5 col-md-6 col-lg-6 mb-2">
                                            <div class="form-floating">
                                                <input type="number" name="percentage" class="form-control" id="floatingPercentage" placeholder="Porcentagem (%):">
                                                <label for="floatingPercentage">Porcentagem (%):</label>
                                            </div>
                                        </div>
                                        <div class="col-5 col-md-12 col-lg-12 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="user_id" class="form-control" id="user_id" placeholder="ID:">
                                                <label for="user_id">ID:</label>
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
                            <form action="{{ route('list-coupons') }}" method="GET">
                                <div class="modal-header">
                                    <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-12 col-md-7 col-lg-7 mb-2">
                                            <div class="form-floating">
                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Informe o Nome:">
                                                <label for="floatingName">Código:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-5 col-lg-5 mb-2">
                                            <div class="form-floating">
                                                <input type="date" name="expiry_date" class="form-control" id="floatingExpiry_date" placeholder="Data de Expiração:">
                                                <label for="floatingExpiry_date">Data de Expiração:</label>
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
                        <div class="table-responsive">
                            <table class="table table-hover" id="table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descrição</th>
                                        <th class="text-center">(%) / Qtd</th>
                                        <th class="text-center">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($coupons as $coupon)
                                        <tr>
                                            <td>
                                                <button title="Clique no código para copiar" class="btn btn-sm btn-outline-dark" onclick="onClip('{{ $coupon->name }}')">{{ $coupon->name }}</button> <br>
                                                @if ($coupon->user) 
                                                    <span class="badge bg-dark">{{ $coupon->user->name }}</span> 
                                                @endif
                                            </th>
                                            <td>
                                                {{ substr($coupon->description, 0, 40) }} <br>
                                                @if($coupon->expiry_date) 
                                                    <span class="badge bg-primary">Expiração: {{ \Carbon\Carbon::parse($coupon->expiry_date)->format('d/m/Y') }} </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ number_format($coupon->percentage, 2, ',', '.') }}% | <span class="badge bg-primary"> {{ $coupon->qtd }} </span>
                                            </td>
                                            <td class="text-center">
                                                <form action="" method="POST" class="delete">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $coupon->id }}"> 
                                                    <div class="btn-group" role="group">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
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
@endsection