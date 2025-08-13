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
                <div class="btn-group mb-3" role="group">
                    @if (Auth::user()->type === 1)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal">Novo Banner</button>
                    @endif
                </div>

                <div id="carouselExampleControls" class="carousel slide mb-2" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($banners as $key => $banner)
                            <div class="carousel-item @if($key == 0) active @endif">
                                <img src="{{ asset('storage/' . $banner->image) }}" class="d-block w-100" alt="{{ $banner->title ?? 'Banner informativo' }}">
                                <div class="carousel-caption d-none d-md-block">
                                    <h5>{{ $banner->title }}</h5>
                                    <p>{{ $banner->description }}</p>
                                </div>
                                @if (Auth::user()->type === 1)
                                    <form action="{{ route('deleted-banner', ['id' => $banner->id]) }}" method="POST" class="mt-2 mb-2 delete text-center">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Excluir</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
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

            <div class="col-sm-12 col-md-8 col-lg-8">
                <div class="row align-items-start">
                    <div class="col-sm-12 col-md-4 col-lg-4">
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

                    <div class="col-sm-12 col-md-4 col-lg-4">
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

                    <div class="col-12 col-sm-12 col-md-4 col-lg-4">
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
                    </div>

                    <div class="col-sm-12 col-md-4 col-lg-4">
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

                    <div class="col-sm-12 col-md-4 col-lg-4">
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

                    <div class="col-12 col-sm-12 col-md-4 col-lg-4">
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

                    <div class="col-12 col-sm-12 col-lg-7">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Ranking <span>| Os 10 melhores faturamentos.</span></h5>
                                
                                <div class="table-responsive">
                                    <table class="table table" id="table">
                                        <thead>
                                            <tr class="table-primary">
                                                <th scope="col" class="text-center">°</th>
                                                <th scope="col">Nome</th>
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

                    <div class="col-12 col-sm-12 col-lg-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Rede <span>| Últimos cadastros</span></h5>
                                
                                <div class="table-responsive">
                                    <table class="table table" id="table">
                                        <thead>
                                            <tr class="table-primary">
                                                <th scope="col">Nome</th>
                                                <th scope="col" class="text-center">Status</th>
                                                <th scope="col" class="text-center">Cadastro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($networks as $key => $network)
                                                <tr>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-4 col-lg-4">
                <div class="row align-items-start">
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

                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="card">
                            @if (Auth::user()->type == 1)
                                <div class="filter">
                                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                        <li class="dropdown-header text-start">
                                            <h6>Opções</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createModal">Nova Publicação</a></li>
                                    </ul>

                                    <div class="modal fade" id="createModal" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('created-post') }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Dados da Publicação</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                <div class="form-floating">
                                                                    <input type="text" name="title" class="form-control" id="title" placeholder="Título:" required>
                                                                    <label for="title">Título:</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                <div class="form-floating">
                                                                    <input type="file" name="image" class="form-control" id="image" placeholder="Imagem (Capa 1024px X 768px):" required>
                                                                    <label for="image">Imagem (Capa 1024px X 768px):</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                <div class="form-floating">
                                                                    <select name="access_type" class="form-select" id="access_type">
                                                                        <option value="">Opções</option>
                                                                        <option value="">Todos</option>
                                                                        <option value="1">Administradores</option>
                                                                        <option value="2">Consultores</option>
                                                                    </select>
                                                                    <label for="access_type">Publicar para:</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                <div id="editor"></div>
                                                                <textarea id="content" name="content" style="display:none;"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer btn-group">
                                                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                                        <button type="submit" class="btn btn-primary">Publicar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="card-body pb-0">
                                <h5 class="card-title">Notícias &amp; Atualizações <span>| Recentes</span></h5>
                                <div class="news">
                                    @foreach ($posts as $post)
                                        <div class="post-item clearfix mb-2">
                                            <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" data-bs-toggle="modal" data-bs-target="#viewModal{{ $post->id }}">
                                            <h4><a href="#" data-bs-toggle="modal" data-bs-target="#viewModal{{ $post->id }}">{{ $post->title }}</a></h4>
                                            <p>{{ $post->labelResume() }}</p>
                                        </div>

                                        <div class="modal fade" id="viewModal{{ $post->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form action="{{ route('deleted-post', ['id' => $post->id]) }}" method="POST" class="delete">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $post->id }}">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Dados da Publicação</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                    {!! $post->content !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if (Auth::user()->type == 1)
                                                            <div class="modal-footer btn-group">
                                                                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                                                                <button type="submit" class="btn btn-primary">Excluir</button>
                                                            </div>
                                                        @endif
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          
        </div>
    </section>

    <div class="modal fade" id="addBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('created-banner') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Dados do Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                <div class="form-floating">
                                    <input type="text" name="title" class="form-control" id="title" placeholder="Título:">
                                    <label for="title">Título:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                <div class="form-floating">
                                    <textarea class="form-control" name="description" placeholder="Descrição:" id="description" style="height: 100px;"></textarea>
                                    <label for="description">Descrição:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                <div class="form-floating">
                                    <input type="text" name="url" class="form-control" id="url" placeholder="URL:">
                                    <label for="url">URL:</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 col-lg-12 mb-2">
                                
                                    <input type="file" name="image" class="form-control" id="image" placeholder="Imagem: (Banner)*" required>
                                    <label for="image">Imagem: (Banner)*</label>
                                
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer btn-group">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': '1' }, { 'header': '2' }, { 'font': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['link', 'blockquote', 'code-block'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }]
                ]
            }
        });

        $('form').submit(function() {
            var content = quill.root.innerHTML;
            $('#content').val(content);
        });
    </script>
@endsection