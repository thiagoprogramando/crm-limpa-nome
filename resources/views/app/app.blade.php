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

                    <div class="col-12 col-sm-12 col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Rank <span>| Dados gerados pelo sistema.</span></h5>
                                 
                                <div class="table-responsive">
                                    <table class="table table table-striped" id="table">
                                        <thead>
                                            <tr class="table-dark">
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
                                                    <td scope="row"><i class="bi bi-award text-primary"></i></td>
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
            </div>
        </div>
    </section>
@endsection