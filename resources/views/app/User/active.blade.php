@extends('app.layout')
@section('title') Gestão de Ativos/Inativos @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Gestão de Ativos/Inativos</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
            <li class="breadcrumb-item active">Gestão de Ativos/Inativos</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Assinaturas {{ date('Y') }}</h5>
                    <div id="lineChart"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
            <div class="card p-2">
                <div class="card-body">
                    <h5 class="card-title">Ativos/Inativos</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="table">
                            <thead>
                                <tr>
                                    <th scope="col">N°</th>
                                    <th scope="col">Nome</th>
                                    <th scope="col">Situação</th>
                                    <th class="text-center" scope="col">Última Mens</th>
                                    <th class="text-center" scope="col">Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <th scope="row">{{ $user->id }}</th>
                                        <td>
                                            {{ implode(' ', array_slice(explode(' ', $user->name), 0, 2)) }}
                                        </td>
                                        <td>{{ $user->statusLabel() }}</td>
                                        <td class="text-center">
                                            <a href="{{ $user->lastPendingInvoiceTypeOneUrl() }}" target="_blank">
                                                {{ $user->lastPendingInvoiceTypeOne() }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('delete-user') }}" method="POST" class="delete btn-group">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $user->id }}">
                                                <button type="submit" class="btn btn-danger text-light"><i class="bi bi-trash"></i></button>
                                                <a href="{{ route('send-active', ['id' => $user->id]) }}" class="btn btn-success text-light"><i class="bi bi-whatsapp"></i></a>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{ asset('assets/dashboard/vendor/apexcharts/apexcharts.min.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {

        const invoicesData = @json($invoicesData);

        new ApexCharts(document.querySelector("#lineChart"), {
            series: [{
                name: "Assinaturas",
                data: invoicesData
            }],
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            }
        }).render();
    });
</script>

@endsection