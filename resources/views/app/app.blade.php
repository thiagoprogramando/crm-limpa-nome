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
        <div class="row">
            <div class="col-sm-12 col-md-7 col-lg-7 row">
                <div class="col-12">
                    <div id="carouselExampleControls" class="carousel slide mb-2" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item carousel-item-next carousel-item-start">
                                <img style="max-height: 300px !important;" src="{{ asset('assets/dashboard/img/marketing/nome_10_um_cupom.png') }}" class="d-block w-100" alt="Envie dez nomes e ganhe um!">
                            </div>
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

                @if (Auth::user()->status <> 1)
                    <div class="col-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-octagon me-1"></i>
                            Você possui pendências no cadastro, <a href="{{ route('profile') }}">complete os dados clicando aqui!</a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="card info-card clock-card">
                        <div class="card-body">
                            <h5 class="card-title">T. Vendas (N°)</h5>

                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-cart"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ $sales }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-6">
                    <a href="{{ route('manager-sale') }}?created_at={{ now()->format('Y-m-d') }}&status=1">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">T. Vendas (Hoje)</h5>

                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background-color: #ff8400; color:#fff;">
                                        <i class="bi bi-cart-check-fill"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $salesDay }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="card info-card clock-card">
                        <div class="card-body">
                            <h5 class="card-title">T. Faturamento (R$)</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background-color: #559eff; color:#fff;">
                                <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ number_format($invoicing, 2, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="card info-card clock-card">
                        <div class="card-body">
                            <h5 class="card-title">T. Faturamento (R$ Hoje)</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background-color: #00FF9C; color:#fff;">
                                <i class="ri-money-dollar-circle-line"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ number_format($invoicingDay, 2, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-5 col-lg-5 row">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <div class="card info-card clock-card">
                        <div class="card-body">
                            <h5 class="card-title">Próxima Lista @if($list) <span>{{ \Carbon\Carbon::parse($list->end)->format('d/m/Y') }}</span> @else --- @endif</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ $remainingTime }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card info-card clock-card">
                        <div class="card-body p-2">
                            <canvas id="pieChart"></canvas>
                            <script>
                                @php
                                    $labels = array_keys($consultant);
                                    $values = array_values($consultant);
                                @endphp
                                document.addEventListener("DOMContentLoaded", () => {
                                    const ctx = document.querySelector('#pieChart');
                                    new Chart(ctx, {
                                        type: 'pie',
                                        data: {
                                            labels: @json($labels),
                                            datasets: [{
                                                data: @json($values),
                                                backgroundColor: [
                                                    'rgb(255, 99, 132)',
                                                    'rgb(54, 162, 235)',
                                                    'rgb(255, 205, 86)',
                                                    'rgb(75, 192, 192)'
                                                ],
                                                hoverOffset: 4
                                            }]
                                        },
                                        options: {
                                            plugins: {
                                                legend: {
                                                    display: false
                                                }
                                            }
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="card info-card clock-card">
                        <div class="card-body">
                            <h5 class="card-title">Ativos/Inativos</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-lines-fill"></i>
                                </div>
                                <div class="ps-3">
                                    <p>
                                        Ativos : {{ $actives }} <br>
                                        Inativo: {{ $inactives }} <br>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="col-sm-12 col-md-12 col-lg-12">
                    <div class="card info-card clock-card">
                        <div class="card-body">
                            <h5 class="card-title">Graduação</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-award"></i>
                                </div>
                                <div class="ps-3">
                                    <p>{{ Auth::user()->levelLabel() }}</p>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" 
                                            style="width: {{ Auth::user()->getGraduation()->progress }}%" 
                                            aria-valuenow="{{ Auth::user()->getGraduation()->progress }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="{{ Auth::user()->getGraduation()->maxSales }}">
                                        </div>
                                        <small>{{ Auth::user()->getGraduation()->progress }}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
       
        <div class="row">
            <div class="col-12 col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ranking <span>| Os 10 melhores faturamentos.</span></h5>
                        
                        <div class="table-responsive">
                            <table class="table table" id="table">
                                <thead>
                                    <tr class="table-primary">
                                        <th scope="col" class="text-center">°</th>
                                        <th scope="col">Consultor</th>
                                        <th scope="col" class="text-center">Estado</th>
                                        <th scope="col">Faturamento</th>
                                        <th scope="col">Graduação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $key => $user)
                                        <tr>
                                            <td scope="row" class="d-flex justify-content-center">
                                                @switch($loop->iteration)
                                                    @case(1)
                                                        <i class="bi bi-award" style="color: #fcef87;"></i>
                                                        @break
                                                    @case(2)
                                                        <i class="bi bi-award" style="color: #4f4f4f;"></i>
                                                        @break
                                                    @case(3)
                                                        <i class="bi bi-award" style="color: #ea7e12;"></i>
                                                        @break
                                                    @default
                                                        <i class="bi bi-award" style="color: #C0C0C0;"></i>
                                                        @break  
                                                @endswitch
                                                @if($user->photo)
                                                    <img src="{{ asset('storage/' . $user->photo) }}" alt="Foto de Perfil" class="rounded-circle" width="30" height="30">
                                                @else
                                                    <img src="{{ asset('assets/dashboard/img/profile_white.png') }}" alt="Foto de Perfil" class="rounded-circle" width="30" height="30">
                                                @endif
                                            </td>
                                            @if ($user->name == Auth::user()->name)
                                                <td>{{ $user->name }}</td>
                                            @else
                                                <td>{{ $user->maskedName() }}</td>
                                            @endif
                                            <td class="text-center">{{ $user->state }}</th>
                                            <td class="text-success">R$ {{ number_format($user->saleTotal(), 2, ',', '.') }}</td>
                                            <td>{{ $user->levelLabel() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Rede <span>| Últimos cadastros</span></h5>
                        
                        <div class="table-responsive">
                            <table class="table table" id="table">
                                <thead>
                                    <tr class="table-primary">
                                        <th scope="col" class="text-center">°</th>
                                        <th scope="col">Consultor</th>
                                        <th scope="col" class="text-center">Status</th>
                                        <th scope="col" class="text-center">Cadastro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($networks as $key => $network)
                                        <tr>
                                            <td scope="row" class="d-flex justify-content-center">
                                                @if($network->photo)
                                                    <img src="{{ asset('storage/' . $network->photo) }}" alt="Foto de Perfil" class="rounded-circle" width="30" height="30">
                                                @else
                                                    <img src="{{ asset('assets/dashboard/img/profile_white.png') }}" alt="Foto de Perfil" class="rounded-circle" width="30" height="30">
                                                @endif
                                            </td>
                                            <td>
                                                @if ($network->name == Auth::user()->name)
                                                    {{ $network->name }}
                                                @else
                                                    {{ $network->maskedName() }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($network->status == 1)
                                                    Ativo
                                                @else
                                                    Pendente
                                                @endif
                                            </th>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($network->created_at)->format('d/m/Y') }}</th>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="text-center">
                                {{ $networks->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection