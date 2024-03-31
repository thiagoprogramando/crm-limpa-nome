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

        <div class="col-lg-8">
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

                <div class="col-xxl-4 col-md-6">
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

                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card revenue-card">

                        <div class="card-body">
                            <h5 class="card-title">T. Comissão (R$)</h5>

                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ number_format($commission, 2, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-xxl-4 col-xl-12">
                    <div class="card info-card customers-card">

                        <div class="card-body">
                            <h5 class="card-title">T. Vendas (R$)</h5>

                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-award"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ number_format($saleValue, 2, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Gráfico de crescimento <span>| Dados gerados pelo sistema.</span></h5>
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

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

            <div class="card">
                <div class="card-body pb-0">
                    <h5 class="card-title">Novidades <span>| Recentes</span></h5>
                    <div class="news">
                        <div class="post-item clearfix">
                            <img src="{{ asset('assets/dashboard/img/news-1.jpg') }}">
                            <h4><a href="#">Cartão para negativado!</a></h4>
                            <p>IFuture Banking promete trazer cartão com micro-crédito para negativados.</p>
                        </div>
                    </div>
                    <div class="news">
                        <div class="post-item clearfix">
                            <img src="{{ asset('assets/dashboard/img/news-1.jpg') }}">
                            <h4><a href="#">Cartão para negativado!</a></h4>
                            <p>IFuture Banking promete trazer cartão com micro-crédito para negativados.</p>
                        </div>
                    </div>
                    <div class="news">
                        <div class="post-item clearfix">
                            <img src="{{ asset('assets/dashboard/img/news-1.jpg') }}">
                            <h4><a href="#">Cartão para negativado!</a></h4>
                            <p>IFuture Banking promete trazer cartão com micro-crédito para negativados.</p>
                        </div>
                    </div>
                    <div class="news">
                        <div class="post-item clearfix">
                            <img src="{{ asset('assets/dashboard/img/news-1.jpg') }}">
                            <h4><a href="#">Cartão para negativado!</a></h4>
                            <p>IFuture Banking promete trazer cartão com micro-crédito para negativados.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<script>
    var salesData = {!! json_encode($saleGraph) !!};
    var commissionsData = {!! json_encode($commissionGraph) !!};

    var labels = [];
    var sales = [];
    var commissions = [];

    for (var i = 0; i < salesData.length; i++) {
        labels.push(salesData[i].month);
        sales.push(salesData[i].totalSales);
    }

    for (var j = 0; j < commissionsData.length; j++) {
        commissions.push(commissionsData[j].totalCommissions);
    }

    var ctx = document.getElementById('growthChart').getContext('2d');
    var growthChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Vendas',
                data: sales,
                borderColor: 'green',
                fill: false
            }, {
                label: 'Comissões',
                data: commissions,
                borderColor: 'blue',
                fill: false
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Crescimento de Vendas e Comissões ao Longo do Tempo'
            }
        }
    });
</script>
@endsection