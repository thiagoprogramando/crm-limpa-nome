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
        <div id="carouselExampleControls" class="carousel slide mb-3" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active carousel-item-start">
                    <img src="{{ asset('assets/dashboard/img/marketing/indicados_5_1_cupom.png') }}" class="d-block w-100" alt="Indique cinco novos afiliados e ganhe uma mensalidade!">
                </div>
                <div class="carousel-item carousel-item-next carousel-item-start">
                    <img src="{{ asset('assets/dashboard/img/marketing/nome_10_um_cupom.png') }}" class="d-block w-100" alt="Envie dez nomes e ganhe um!">
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

        @if(empty($dashboard))
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">

                        @if (Auth::user()->status <> 1)
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

                        <div class="col-sm-12 col-md-4 col-lg-3">
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

                        <div class="col-sm-12 col-md-4 col-lg-3">
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

            <div class="col-12 col-sm-12 col-lg-12 p-3 row">
                <div id="fees" class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="card p-3">
                        <div class="card-body">
                            <p class="card-title mb-0">Formas de Pagamento</p>
                            <small>Cobranças avulsas, parceladas, assinaturas e link de pagamento</small>
                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <p class="lead">
                                        <i class="bi bi-upc"></i> Boleto Bancário <br>
                                        <small class="text-success">Recebimento em até 3 dias úteis após o pagamento.</small>
                                    </p> 
                                </div>
                                <div class="col-12">
                                    <p class="lead">
                                        <i class="bi bi-credit-card"></i> Cartão de Crédito <br>
                                        <small class="text-success">Recebimento em 32 dias após o pagamento.</small>
                                    </p> 
                                </div>
                                <div class="col-12">
                                    <p class="lead">
                                        <i class="bi bi-credit-card-2-back-fill"></i> Cartão de Débito <br>
                                        <small class="text-success">Recebimento em 3 dias após o pagamento.</small>
                                    </p> 
                                </div>
                                <div class="col-12">
                                    <p class="lead">
                                        <i class="ri-qr-code-line"></i> Pix <br>
                                        <small class="text-success">Recebimento em poucos segundos após o pagamento.</small>
                                    </p> 
                                </div>
                                <p>
                                    <b class="text-danger">Atenção:</b> Será descontado 5% por boleto/pix emitido, caso sejam efetuadas notificações extras (R$ 0,99) adicionais.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="card p-3">
                        <div class="card-body">
                            <h5 class="card-title">Perguntas frequentes</h5>
                            <div>
                                <h6 class="text-primary">1. É possível antecipar vendas realizadas no cartão?</h6>
                                <p>
                                    Sim, imediatamente após a conclusão do processo de venda, nosso sistema tentará realizar a antecipação das parcelas junto ao banco. 
                                    Isso permite que você receba o valor total de forma mais rápida.
                                </p>
                                <p>
                                    <b class="text-danger">Atenção:</b> A aprovação da antecipação depende do banco e pode não ser autorizada, mantendo os prazos padrões de repasse.
                                </p>
                            </div>
                            <div class="pt-2">
                                <h6 class="text-primary">2. Qual é o prazo para receber as comissões?</h6>
                                <p>
                                    Boletos podem levar até três (3) dias úteis para compensação. Por padrão, o banco realiza remessas nos seguintes horários (horário de Brasília): 18h e 00h.
                                </p>
                                <p>
                                    <b class="text-danger">Atenção:</b> As vendas podem ser aprovadas assim que o banco confirma a conciliação, porém os valores serão repassados nas datas subsequentes.
                                </p>
                            </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection