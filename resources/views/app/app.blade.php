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

        @if(Auth::user()->type <> 1)
            <div class="card p-3" data-bs-toggle="modal" data-bs-target="#cruzeiroModal">
                <p class="lead"><i class="bi bi-bell text-warning"></i> Promoção Cruzeiro! Com 100 vendas cabine s/acompanhante ou 200 vendas c/acompanhante!</p>
                <div class="progress mt-3 mb-3" style="height: 25px;">
                    <div class="progress-bar text-light text-center" role="progressbar" style="width: {{ Auth::user()->promoCruzeiro() }}%" aria-valuenow="{{ Auth::user()->promoCruzeiro() }}" aria-valuemin="0" aria-valuemax="100">
                        Você tem {{ Auth::user()->promoCruzeiro() }} vendas confirmadas.
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="cruzeiroModal" tabindex="-1" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                        <img class="img-fluid" src="{{ asset('assets/dashboard/img/cruzeiro.jpg') }}" alt="Cruzeiro">
                    </div>
                    <div class="modal-footer btn-group">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
       
        @if(empty($dashboard))
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">

                        @if (Auth::user()->phone == null || Auth::user()->wallet == null || Auth::user()->api_key == null)
                            <div class="col-12">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-octagon me-1"></i>
                                    Você possui pendências no cadastro, <a href="{{ route('profile') }}">complete os dados clicando aqui!</a>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif
                        
                        @if(Auth::user()->type == 5)
                            <div class="col-12">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Agora você pode montar o seu time e lucrar 20% De todos contratos deles.
                                    <a href="{{ route('registrer', ['id' => Auth::user()->id]) }}" target="_blank">Você pode indicar clicando aqui.</a>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        <div class="col-sm-12 col-md-4 col-lg-3">
                            <div class="card info-card sales-card">
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
                            <div class="card info-card sales-card">
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
                        </div>

                        <div class="col-sm-12 col-md-4 col-lg-3">
                            <div class="card info-card revenue-card">

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
                            <div class="card info-card customers-card">

                                <div class="card-body">
                                    <h5 class="card-title">Graduação</h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-award"></i>
                                        </div>
                                        <div class="ps-3">
                                            <p>{{ Auth::user()->levelLabel() }}</p>
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

                        <div class="col-12 col-sm-12 col-lg-6">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">Nível Atual</h5>
                                    
                                    <small>{{ $nivel }}</small>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progressAtual }}%" aria-valuenow="{{ $progressAtual }}" aria-valuemin="0" aria-valuemax="{{ $maxSalesAtual }}"></div>
                                        <small>Faltam {{ max(0, $maxSalesAtual - $saleTotal) }} vendas para o próximo nível</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-lg-6">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">Próxima Lista @if($list) <span>{{ \Carbon\Carbon::parse($list->end)->format('d/m/Y') }}</span> @else Não há lista disponível @endif</h5>
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
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-sm-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="card-header text-dark" style="background-color: #F5F5F5;">
                            <b><i class="bi bi-grid"></i> Dados</b>
                        </div>

                        <div class="card info-card sales-card text-center">
                            <div class="card-body">
                                <h5 class="card-title">T. Vendas (Hoje)</h5>

                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-cart-check-fill"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $salesDay }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card info-card clock-card text-center">
                            <div class="card-body">
                                <h5 class="card-title">Próxima Lista @if($list) <span>{{ \Carbon\Carbon::parse($list->end)->format('d/m/Y') }}</span> @else Não há lista disponível @endif</h5>
                                <div class="d-flex align-items-center justify-content-center">
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
                </div>

                <div class="col-sm-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="card-header text-dark" style="background-color: #F5F5F5;">
                            <b><i class="bi bi-person-fill"></i> Cobranças</b>
                        </div>

                        <div class="card-body p-2">
                            <div class="card-dashbord p-1 m-3">
                                <p class="m-1">PREVISTAS</p>
                                <h1 class="display-6 text-warning text-center"><b>{{ $invoices['previstas'] }}</b></h1>
                            </div>
                            
                            <div class="card-dashbord p-1 m-3">
                                <p class="m-1">INADIMPLENTES</p>
                                <h1 class="display-6 text-danger text-center"><b>{{ $invoices['vencidas'] }}</b></h1>
                            </div>

                            <div class="card-dashbord p-1 m-3">
                                <p class="m-1">RECEBIDAS</p>
                                <h1 class="display-6 text-success text-center"><b>{{ $invoices['recebidas'] }}</b></h1>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="card-header text-dark" style="background-color: #F5F5F5;">
                            <b><i class="bi bi-graph-up"></i> Faturamento</b>
                        </div>

                        <div class="card-body p-2">
                            <div class="card-dashbord p-1 m-3">
                                <p class="m-1">PREVISTAS</p>
                                <h1 class="text-warning text-center">
                                    <b>
                                        <small style="font-size: 16px;">R$</small> 
                                        {{ number_format($invoicing['previstas'], 2, ',', '.') }}
                                    </b>
                                </h1>
                            </div>
                            
                            <div class="card-dashbord p-1 m-3">
                                <p class="m-1">INADIMPLENTES</p>
                                <h1 class="text-danger text-center">
                                    <b>
                                        <small style="font-size: 16px;">R$</small> 
                                        {{ number_format($invoicing['vencidas'], 2, ',', '.') }}
                                    </b>
                                </h1>
                            </div>

                            <div class="card-dashbord p-1 m-3">
                                <p class="m-1">RECEBIDAS</p>
                                <h1 class="text-success text-center">
                                    <b>
                                        <small style="font-size: 16px;">R$</small> 
                                        {{ number_format($invoicing['recebidas'], 2, ',', '.') }}
                                    </b>
                                </h1>
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
                        <h5 class="card-title">Rank <span>| Dados gerados pelo sistema.</span></h5>
                        
                        <div class="table-responsive">
                            <table class="table table" id="table">
                                <thead>
                                    <tr class="table-primary">
                                        <th scope="col">--</th>
                                        <th scope="col">N°</th>
                                        <th scope="col">Vendedor</th>
                                        <th scope="col">Faturamento</th>
                                        <th scope="col">Qtd</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $key => $user)
                                        <tr>
                                            <td scope="row">
                                                @switch($loop->iteration)
                                                    @case(1)
                                                        <i class="bi bi-award" style="color: #FFD700;"></i>
                                                        @break
                                                    @case(2)
                                                        <i class="bi bi-award" style="color: #C0C0C0;"></i>
                                                        @break
                                                    @case(3)
                                                        <i class="bi bi-award" style="color: #A62A2A;"></i>
                                                        @break
                                                    @default
                                                        <i class="bi bi-award text-primary"></i>
                                                        @break  
                                                @endswitch
                                            </td>
                                            <th scope="row">{{ $loop->iteration }}</th>
                                            <td>{{ $user->name }}</td>
                                            <td>R$ {{ number_format($user->saleTotal(), 2, ',', '.') }}</td>
                                            <td>{{ $user->saleCount() }}</td>
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
@endsection