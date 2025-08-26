@extends('app.layout')
@section('title') Dashboard @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    
    <section class="section dashboard">
        <div class="row align-items-start">
            
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="row align-items-start">
                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">Total (R$)</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background-color: #00FF9C; color:#fff;">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ "R$ ".number_format($total, 2, ',', '.') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-3">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $status }}</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $users->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-body p-0 m-0">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="table">
                                        <thead>
                                            <tr class="table-primary">
                                                <th scope="col" class="text-center">°</th>
                                                <th scope="col">Nome</th>
                                                <th scope="col" class="text-center">Estado</th>
                                                <th scope="col">Faturamento</th>
                                                <th scope="col">Graduação</th>
                                                <th scope="col">Opções</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $key => $position)
                                                <tr>
                                                    <td scope="row" class="d-flex justify-content-center">
                                                        #{{ $position->id }} &nbsp;
                                                        <img src="{{ $position->photo ? asset('storage/' . $position->photo) :  asset('assets/img/profile_white.png') }}" alt="Foto de Perfil" class="rounded-circle" width="30" height="30">
                                                    </td>
                                                    <td>
                                                        {{ $position->maskedName() }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $position->state }}
                                                    </th>
                                                    <td class="text-success">
                                                        R$ {{ number_format($position->saleTotal(), 2, ',', '.') }}
                                                    </td>
                                                    <td>
                                                        {{ $position->getGraduation()->nivel }}
                                                    </td>
                                                    <td>
                                                        @if ($status == 'Inativos')
                                                            <a href="{{ route('notification-recurrence', ['id' => $position->id]) }}" class="btn btn-outline-primary">Disparar Notificação</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection