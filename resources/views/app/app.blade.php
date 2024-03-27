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
                            <h5 class="card-title">Vendas</h5>

                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-cart"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>145</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4 col-md-6">
                    <div class="card info-card revenue-card">

                        <div class="card-body">
                            <h5 class="card-title">Comissão</h5>

                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>R$ 3.262</h6>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-xxl-4 col-xl-12">
                    <div class="card info-card customers-card">

                        <div class="card-body">
                            <h5 class="card-title">Clientes</h5>

                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>1244</h6>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-12">
                    <div class="card">

                        <div class="card-body">
                            <h5 class="card-title">Gráfico de crescimento <span>| Dados gerados pelo sistema.</span></h5>

                            <div id="reportsChart"></div>
                            <script>
                                document.addEventListener("DOMContentLoaded", () => {
                                    new ApexCharts(document.querySelector("#reportsChart"), {
                                        series: [{
                                            name: 'Sales',
                                            data: [31, 40, 28, 51, 42, 82, 56],
                                        }, {
                                            name: 'Revenue',
                                            data: [11, 32, 45, 32, 34, 52, 41]
                                        }, {
                                            name: 'Customers',
                                            data: [15, 11, 32, 18, 9, 24, 11]
                                        }],
                                        chart: {
                                            height: 350,
                                            type: 'area',
                                            toolbar: {
                                                show: false
                                            },
                                        },
                                        markers: {
                                            size: 4
                                        },
                                        colors: ['#4154f1', '#2eca6a', '#ff771d'],
                                        fill: {
                                            type: "gradient",
                                            gradient: {
                                                shadeIntensity: 1,
                                                opacityFrom: 0.3,
                                                opacityTo: 0.4,
                                                stops: [0, 90, 100]
                                            }
                                        },
                                        dataLabels: {
                                            enabled: false
                                        },
                                        stroke: {
                                            curve: 'smooth',
                                            width: 2
                                        },
                                        xaxis: {
                                            type: 'datetime',
                                            categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
                                        },
                                        tooltip: {
                                            x: {
                                                format: 'dd/MM/yy HH:mm'
                                            },
                                        }
                                    }).render();
                                });
                            </script>
                        </div>

                    </div>
                </div>
        

            </div>
        </div>

        <div class="col-lg-4">

            <div class="card info-card clock-card">
                <div class="card-body">
                    <h5 class="card-title">Próxima Lista <span> 14/03/2024</span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="ps-3">
                            <h6>5d 2h 33min</h6>
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

@endsection