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
        @if(empty($dashboard))
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">

                        @if (Auth::user()->status <> 1 || Auth::user()->wallet == null || Auth::user()->api_key == null)
                            <div class="col-12">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-octagon me-1"></i>
                                    Você possui pendências no cadastro, <a href="{{ route('profile') }}">complete os dados clicando aqui!</a>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        <div class="col-sm-12 col-md-4 col-lg-3">
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

                        <div class="col-sm-12 col-md-4 col-lg-3">
                            <a href="{{ route('manager-sale') }}?created_at={{ now()->format('Y-m-d') }}&status=1">
                                <div class="card info-card clock-card">
                                    <div class="card-body">
                                        <h5 class="card-title">T. Vendas (Hoje)</h5>

                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
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

                        <div class="col-sm-12 col-md-4 col-lg-3">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">T. Faturamento (R$)</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-currency-dollar"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>{{ number_format($invoicing, 2, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-4 col-lg-3">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">T. Faturamento (R$ Hoje)</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-money-dollar-circle-line"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>{{ number_format($invoicingDay, 2, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-3 col-lg-3">
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

                        <div class="col-12 col-sm-12 col-md-4 col-lg-3">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">T. de Consultores</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm table-striped text-center" style="font-size: 12px !important; margin: 0;">
                                                <thead>
                                                    <tr>
                                                        <th>Consultor</th>
                                                        <th>Líder</th>
                                                        <th>Regional</th>
                                                        <th>Gerente</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>{{ $consultant['CONSULTOR'] }}</td>
                                                        <td>{{ $consultant['LIDER'] }}</td>
                                                        <td>{{ $consultant['REGIONAL'] }}</td>
                                                        <td>{{ $consultant['GERENTE'] }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="col-12 col-sm-12 col-md-4 col-lg-3">
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

                        @php
                            $saleTotal = Auth::user()->saleCount();
                            
                            $maxSalesConsultor = 2;
                            $maxSalesConsultorLider = 10;
                            $maxSalesRegional = 50;
                            $maxSalesRegionalLider = 100;

                            $progressConsultor = min(100, ($saleTotal / $maxSalesConsultor) * 100);
                            $progressConsultorLider = min(100, ($saleTotal / $maxSalesConsultorLider) * 100);
                            $progressRegional = min(100, ($saleTotal / $maxSalesRegional) * 100);
                            $progressRegionalLider = min(100, ($saleTotal / $maxSalesRegionalLider) * 100);

                            $nivel = '';
                            $progressAtual = 0;
                            $maxSalesAtual = 0;

                            if ($saleTotal < $maxSalesConsultor) {
                                $nivel = 'Consultor';
                                $progressAtual = $progressConsultor;
                                $maxSalesAtual = $maxSalesConsultor;
                            } elseif ($saleTotal < $maxSalesConsultorLider) {
                                $nivel = 'Consultor Líder';
                                $progressAtual = $progressConsultorLider;
                                $maxSalesAtual = $maxSalesConsultorLider;
                            } elseif ($saleTotal < $maxSalesRegional) {
                                $nivel = 'Regional';
                                $progressAtual = $progressRegional;
                                $maxSalesAtual = $maxSalesRegional;
                            } elseif ($saleTotal < $maxSalesRegionalLider) {
                                $nivel = 'Gerente Regional';
                                $progressAtual = $progressRegionalLider;
                                $maxSalesAtual = $maxSalesRegionalLider;
                            }
                        @endphp

                        <div class="col-sm-12 col-md-4 col-lg-3">
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
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progressAtual }}%" aria-valuenow="{{ $progressAtual }}" aria-valuemin="0" aria-valuemax="{{ $maxSalesAtual }}"></div>
                                                <small>{{ $progressAtual }}%</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
        <div class="row">
            <div class="col-lg-12">
                <div class="row">

                    <div class="col-12 col-sm-12 col-md-4 col-lg-3">
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

                    <div class="col-12 col-sm-12 col-md-4 col-lg-3">
                        <a href="{{ route('manager-sale') }}?created_at={{ now()->format('Y-m-d') }}&status=1">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">T. Vendas (Hoje)</h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
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

                    <div class="col-12 col-sm-12 col-md-4 col-lg-3">
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

                    <div class="col-12 col-sm-12 col-md-4 col-lg-3">
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

                    <div class="col-12 col-sm-12 col-md-4 col-lg-3">
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

                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">T. de Consultores</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </div>
                                    <div class="ps-3">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm table-striped text-center" style="font-size: 14px !important; margin: 0;">
                                                <thead>
                                                    <tr>
                                                        <th>Consultor</th>
                                                        <th>Líder</th>
                                                        <th>Regional</th>
                                                        <th>Gerente</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>{{ $consultant['CONSULTOR'] }}</td>
                                                        <td>{{ $consultant['LIDER'] }}</td>
                                                        <td>{{ $consultant['REGIONAL'] }}</td>
                                                        <td>{{ $consultant['GERENTE'] }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-12 col-md-4 col-lg-3">
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

                </div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ranking <span>| Dados gerados pelo sistema.</span></h5>
                        
                        <div class="table-responsive">
                            <table class="table table" id="table">
                                <thead>
                                    <tr class="table-primary">
                                        <th scope="col" class="text-center">°</th>
                                        <th scope="col">Vendedor</th>
                                        <th scope="col" class="text-center">Estado</th>
                                        <th scope="col">Faturamento</th>
                                        <th scope="col">Graduação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $key => $user)
                                        <tr>
                                            <td scope="row" class="text-center">
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
                                                @if(Auth::user()->photo)
                                                    <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto de Perfil" class="rounded-circle" width="30">
                                                @else
                                                    <img src="{{ asset('assets/dashboard/img/profile_white.png') }}" alt="Foto de Perfil" class="rounded-circle" width="30">
                                                @endif
                                            </td>
                                            @if ($user->name == Auth::user()->name)
                                                <td>{{ $user->name }}</td>
                                            @else
                                                <td>{{ $user->maskedName() }}</td>
                                            @endif
                                            <td class="text-center">{{ $user->state }}</th>
                                            <td>R$ {{ number_format($user->saleTotal(), 2, ',', '.') }}</td>
                                            <td>{{ $user->levelLabel() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .circular-progress {
            position: relative;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: conic-gradient(#004AAD calc(var(--percentage) * 1%), #dee4f0 0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #000000;
        }
    
        .percentage-text {
            position: absolute;
            font-weight: bold;
            color: #000000;
        }
    </style>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const progressElement = document.querySelector('.circular-progress');
            const sales = parseInt(progressElement.getAttribute('data-sales'), 10) || 0;
            const percentage = Math.min((sales / 100) * 100, 100);
  
            progressElement.style.setProperty('--percentage', percentage);
            document.getElementById('percentage').textContent = Math.round(percentage) + '%';
        });
    </script>
@endsection