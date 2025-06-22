@extends('app.layout')
@section('title') Tickets @endsection
@section('conteudo')

<div class="pagetitle">
    <h1>Tickets</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Tickets</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">
    <div class="row">
        <div class="col-12">

            @if (Auth::user()->type == 1)
                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">Filtrar</button>

                    <div class="modal fade" id="filterModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('list-tickets') }}" method="GET">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Filtrar dados da pesquisa</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating">
                                                    <input type="text" name="user_id" class="form-control" id="user_id" placeholder="ID Usuário:">
                                                    <label for="user_id">ID Usuário:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating">
                                                    <input type="date" name="created_at" class="form-control" id="created_at" placeholder="Data de abertura:">
                                                    <label for="created_at">Data de abertura:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                <div class="form-floating">
                                                    <select name="status" class="form-select" id="floatingStatus">
                                                        <option selected value="1">Status:</option>
                                                        <option value="1">Resolvido</option>
                                                        <option value="2">Pendente</option>
                                                    </select>
                                                    <label for="floatingStatus">Status</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer btn-group">
                                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body m-0 p-0">
                    <div class="accordion" id="accordionTicket">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">Abrir novo Ticket</button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionTicket">
                                <div class="accordion-body">
                                    <form action="{{ route('created-ticket') }}" method="POST" class="row">
                                        @csrf
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-127">
                                            <div class="form-floating mb-2">
                                                <textarea name="problem" class="form-control" placeholder="Descreva sua Dúvida/Problema" id="problem" style="height: 150px;"></textarea>
                                                <label for="problem">Descreva sua Dúvida/Problema:</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-12 offset-md-6 col-md-6 offset-lg-8 col-lg-4 d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Enviar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @foreach ($tickets as $ticket)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne{{ $ticket->id }}" aria-expanded="false" aria-controls="collapseOne{{ $ticket->id }}">
                                        @if ($ticket->status == 1)
                                            <span class="badge rounded-pill bg-success">Resolvido</span>
                                        @else
                                            <span class="badge rounded-pill bg-warning">Pendente</span>
                                        @endif 
                                        {{ $ticket->user->name }} 
                                    </button>
                                </h2>
                                <div id="collapseOne{{ $ticket->id }}" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionTicket">
                                    <div class="accordion-body">
                                        <form action="{{ route('updated-ticket', ['id' => $ticket->id]) }}" method="POST" class="row">
                                            @csrf
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-127">
                                                <div class="form-floating mb-2">
                                                    <textarea name="problem" class="form-control" placeholder="Descreva sua Dúvida/Problema" id="problem" style="height: 150px;">{{ $ticket->problem }}</textarea>
                                                    <label for="problem">Descreva sua Dúvida/Problema:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-127">
                                                <div class="form-floating mb-2">
                                                    <textarea name="resolution" class="form-control" placeholder="Descreva sua Dúvida/resolutiona" id="resolution" style="height: 150px;" @disabled(Auth::user()->type == 2)>{{ $ticket->resolution }}</textarea>
                                                    <label for="resolution">Resposta/Solução:</label>
                                                </div>
                                            </div>
                                            @if (Auth::user()->type !== 2)
                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                    <div class="form-floating">
                                                        <select name="status" class="form-select" id="status">
                                                            <option selected value="1">Status:</option>
                                                            <option value="1" @selected($ticket->status == 1)>Resolvido</option>
                                                            <option value="2" @selected($ticket->status == 2)>Pendente</option>
                                                        </select>
                                                        <label for="status">Status</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-12 offset-md-6 col-md-6 offset-lg-8 col-lg-4 d-grid gap-2">
                                                    <button type="submit" class="btn btn-primary">Enviar</button>
                                                </div>
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center">
                        {{ $tickets->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection