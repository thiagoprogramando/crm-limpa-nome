@extends('app.layout')
@section('title') Dashboard @endsection
@section('conteudo')
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    
    <section class="section dashboard">
        <div class="row">
            
            <div class="col-sm-12 col-md-12 col-lg-12">

                @if (Auth::user()->type == 1)
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bannerModal">Novo Banner</button>

                        <div class="modal fade" id="bannerModal" tabindex="-1">
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
                                                        <input type="file" name="image" class="form-control" id="image" placeholder="Imagem (Capa 1024px X 768px):">
                                                        <label for="image">Imagem (Capa 1000px X 300px):</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                    <div class="form-floating">
                                                        <input type="text" name="link" class="form-control" id="link" placeholder="Link:">
                                                        <label for="link">Link:</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                    <div class="form-floating">
                                                        <input type="text" name="content_banner" class="form-control" id="content_banner" placeholder="Legenda:">
                                                        <label for="content_banner">Legenda:</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6 col-lg-6">
                                                    <div class="form-floating mb-2">
                                                        <select name="access_type" class="form-select" id="access_type">
                                                            <option value="">Publicar para:</option>
                                                            <option value="">Todos</option>
                                                            <option value="1">Administradores</option>
                                                            <option value="2">Consultores</option>
                                                            <option value="99">Sócios</option>
                                                        </select>
                                                        <label for="access_type">Opções</label>
                                                    </div>
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

                <div class="card">
                    @isset($banners)
                        <div id="carouselExampleCaptions" class="carousel slide pointer-event" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach ($banners as $key => $banner)
                                    <div class="carousel-item @if($key ==0)  active @endif">
                                        <img src="{{ asset('storage/' . $banner->image) }}" class="d-block w-100 darkened-img" alt="...">
                                        <div class="carousel-caption d-none d-md-block">
                                            <p>{{ $banner->content }}</p>
                                            @if (!empty($banner->link))
                                                <a href="{{ $banner->link }}" target="_blank" class="btn btn-outline-light">Acessar</a>
                                            @endif
                                            @if (Auth::user()->type == 1)
                                                <a href="{{ route('deleted-banner', ['id' => $banner->id]) }}" class="btn btn-outline-light">Excluir</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próxima</span>
                            </button>
                        </div>
                    @endisset
                </div>
            </div>

            <div class="col-sm-12 col-md-12 col-lg-7">
                <div class="row align-items-start">
                    @if (Auth::user()->status === 2)
                        <div class="col-12">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-octagon me-1"></i>
                                Você possui pendências no cadastro, <a href="{{ route('profile') }}">complete os dados clicando aqui!</a>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">Vendas (Geral)</h5>

                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-cart"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ Auth::user()->sales->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <a href="{{ route('list-sales', ['created_at' => now()->format('Y-m-d'), 'status' => 1]) }}">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">Vendas (Hoje)</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-cart-check-fill"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>
                                                {{ Auth::user()->sales->where('created_at', '>=', \Carbon\Carbon::today())->where('status', 1)->count() }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">Graduação</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-award"></i>
                                    </div>
                                    <div class="ps-3">
                                        <p>{{ Auth::user()->getGraduation()->level }}</p>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ Auth::user()->getGraduation()->progress }}%" aria-valuenow="{{ Auth::user()->getGraduation()->progress }}" aria-valuemin="0" aria-valuemax="{{ Auth::user()->getGraduation()->maxSales }}">
                                            </div>
                                            <small>{{ Auth::user()->getGraduation()->progress }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">Geral (R$)</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-currency-dollar"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ number_format(Auth::user()->sales->flatMap(function ($sale) { return $sale->invoices; })->sum('value'), 2, ',', '.') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">Hoje (R$)</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="ri-money-dollar-circle-line"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ number_format(Auth::user()->sales->where('created_at', '>=', \Carbon\Carbon::today())->flatMap(function ($sale) { return $sale->invoices; })->sum('value'), 2, ',', '.') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">Próxima Lista</h5>
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
                                <h5 class="card-title">Ranking <span>| Os 10 melhores Vendedores.</span></h5>
                                
                                <div class="table-responsive">
                                    <table class="table table" id="table">
                                        <thead>
                                            <tr class="table-primary">
                                                <th scope="col" class="text-center">°</th>
                                                <th scope="col">Consultor</th>
                                                <th scope="col" class="text-center">Vendas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rankings as $key => $user)
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
                                                            <img src="{{ asset('assets/img/profile_white.png') }}" alt="Foto de Perfil" class="rounded-circle" width="30" height="30">
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->maskedName() }}</td>
                                                    <td class="text-success text-center">{{ $user->sales->count() }}</td>
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

            <div class="col-sm-12 col-md-12 col-lg-5 col-xl-5">
                @if (Auth::user()->type == 1 || Auth::user()->type == 99)
                    <div class="row align-items-start">
                        <div class="col-12 col-sm-5 col-md-5 col-lg-12">
                            <div class="card info-card clock-card">
                                <div class="card-body">
                                    <h5 class="card-title">Assinantes</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </div>
                                        <div class="ps-3">
                                            <p>
                                                Ativos : {{ $subscribers['actives'] }} <br>
                                                Inativo: {{ $subscribers['inactives'] }} <br>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-7 col-md-7 col-lg-12">
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
                                                                        <input type="file" name="image" class="form-control" id="image" placeholder="Imagem (Capa 1024px X 768px):">
                                                                        <label for="image">Imagem (Capa 1024px X 768px):</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="title" class="form-control" id="title" placeholder="Título:">
                                                                        <label for="title">Título:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6 col-lg-6 mb-2">
                                                                    <div class="form-floating">
                                                                        <input type="text" name="tags" class="form-control" id="tag" placeholder="Tags:">
                                                                        <label for="tag">Tags:</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-6 col-lg-6">
                                                                    <div class="form-floating mb-2">
                                                                        <select name="access_type" class="form-select" id="access_type">
                                                                            <option value="">Publicar para:</option>
                                                                            <option value="">Todos</option>
                                                                            <option value="1">Administradores</option>
                                                                            <option value="2">Consultores</option>
                                                                            <option value="99">Sócios</option>
                                                                        </select>
                                                                        <label for="access_type">Opções</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                        <div id="editor"></div>
                                                                        <textarea id="content" name="content" style="display:none;"></textarea>
                                                                    </div>
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
                                            <div class="post-item clearfix">
                                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}">
                                                <h4><a href="#" data-bs-toggle="modal" data-bs-target="#viewModal{{ $post->id }}">{{ $post->title }}</a></h4>
                                                <p>{{ $post->labelResume() }}</p>
                                            </div>

                                            <div class="modal fade" id="viewModal{{ $post->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <form action="{{ route('deleted-post') }}" method="POST" class="delete">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $post->id }}">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Dados da Publicação</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-12 col-md-12 col-lg-12 mb-2">
                                                                        {!! $post->labelTags() !!}
                                                                    </div>
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
                @else
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="card info-card clock-card">
                            <div class="card-body">
                                <h5 class="card-title">Parceiros <span>| Últimos Associados</span></h5>
                                
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
                                                            <img src="{{ asset('assets/img/profile_white.png') }}" alt="Foto de Perfil" class="rounded-circle" width="30" height="30">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $network->maskedName() }}
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
                @endif
            </div>
        </div>
    </section>

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