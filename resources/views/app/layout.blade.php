<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <title>@yield('title') - {{ env('APP_NAME') }}</title>
        <meta content="" name="description">
        <meta content="" name="keywords">

        <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">

        <link href="https://fonts.gstatic.com" rel="preconnect">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

        <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

        <script src="{{ asset('assets/js/chart.js')}}"></script>
        <script src="{{ asset('assets/js/sweetalert.js')}}"></script>
        <script src="{{ asset('assets/js/jquery.js') }}"></script>
    </head>

    <body>

        <header id="header" class="header fixed-top d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('app') }}" class="logo d-flex align-items-center">
                    <img src="{{ asset('assets/img/logo.png') }}">
                </a>
                <i class="bi bi-list toggle-sidebar-btn text-white"></i>
            </div>

            <nav class="header-nav ms-auto">
                <ul class="d-flex align-items-center">

                    <li class="nav-item">
                        <a class="nav-link nav-icon" href="#">
                            <i class="bi bi-alarm text-white"></i> 
                            <span class="badge bg-primary badge-number">{{ Auth::user()->timeMonthly() }}</span>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-bell text-white"></i>
                            <span class="badge bg-primary badge-number">{{ $notifications->count() }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                            <li class="dropdown-header"> Você possui {{ $notifications->count() }} notificações </li>
                            <li><hr class="dropdown-divider"></li>

                            @foreach ($notifications as $notification)
                                <a href="#">
                                    <li class="notification-item">
                                        @if($notification->type == 1)
                                            <i class="bi bi-check-circle text-success"></i>
                                        @elseif ($notification->type == 2)
                                            <i class="bi bi-exclamation-circle text-warning"></i>
                                        @else
                                            <i class="bi bi-exclamation-diamond text-danger"></i>
                                        @endif
                                        <div>
                                            <h4>{{ $notification->name }}</h4>
                                            <p>{{ $notification->description }}</p>
                                        </div>
                                    </li>
                                </a>
                                <li> <hr class="dropdown-divider"> </li>
                            @endforeach
                            <li class="dropdown-footer"> <a href="#">Não há mais nada aqui.</a> </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown pe-3">
                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                            @if(Auth::user()->photo)
                                <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto de Perfil" class="rounded-circle">
                            @else
                                <img src="{{ asset('assets/img/profile_white.png') }}" alt="Foto de Perfil" class="rounded-circle">
                            @endif
                            
                            <span class="dropdown-toggle text-white ps-2 d-none d-sm-block">{{ Auth::user()->maskedName() }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                            <li class="dropdown-header">
                                <h6>{{ Auth::user()->maskedName() }}</h6>
                                <span>{{ Auth::user()->levelLabel() }}</span>
                            </li>
                            <li> <hr class="dropdown-divider"> </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                                    <i class="bi bi-person"></i>
                                    <span>Perfil</span>
                                </a>
                            </li>
                            <li> <hr class="dropdown-divider"> </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Sair</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </header>

        <aside id="sidebar" class="sidebar">
            <ul class="sidebar-nav" id="sidebar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('app') }}"> <i class="bi bi-grid"></i> <span>Dashboard</span> </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link collapsed" href="https://servicos.ehmconsultas.com/index.php" target="_blank"> <i class="bi bi-search"></i> <span>Consultas</span> </a>
                </li>

                <li class="nav-heading">Gestão de Vendas</li>
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-briefcase"></i><span>Enviar Nome</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                        @foreach($business as $busines)
                            <li><a href="{{ route('create-sale', ['product' => $busines->id]) }}"> <i class="bi bi-circle"></i><span>{{ $busines->name }}</span> </a></li>
                        @endforeach
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'list-sales' || Route::currentRouteName() == 'coupons' ? '' : 'collapsed' }}" data-bs-target="#forms-sale" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bag"></i><span>Vendas</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-sale" class="nav-content collapse {{ Route::currentRouteName() == 'list-sales' || Route::currentRouteName() == 'coupons' ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                        <li> <a href="{{ route('list-sales') }}"> <i class="bi bi-circle"></i><span>Vendas</span> </a> </li>
                        <li><a href="{{ route('list-coupons') }}"> <i class="bi bi-circle"></i><span>Cupons</span> </a></li>
                    </ul>
                </li>
                
                <li class="nav-heading">Gestão de Pessoas</li>
                @if (Auth::user()->type == 1 || Auth::user()->type == 99)
                    <li class="nav-item">
                        <a class="nav-link collapsed" href="{{ route('list-network') }}"> <i class="bi bi-person-lines-fill"></i> <span>Parceiros</span> </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'list-active' ? '' : 'collapsed' }}" data-bs-target="#forms-actvie" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-person-bounding-box"></i><span>Assinantes</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="forms-actvie" class="nav-content collapse {{ Route::currentRouteName() == 'list-active' ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                            <li> <a href="{{ route('list-active', ['status' => 1]) }}"><i class="bi bi-circle"></i><span>Ativos</span></a> </li>
                            <li> <a href="{{ route('list-active', ['status' => 2]) }}"><i class="bi bi-circle"></i><span>Inativos</span></a> </li>
                        </ul>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('list-client') }}"> <i class="bi bi-file-earmark-person"></i> <span>Clientes</span> </a>
                </li>

                @if (Auth::user()->type !== 4)
                    <li class="nav-heading">Gestão Financeira</li>
                    <li class="nav-item">
                        <a class="nav-link collapsed" href="{{ route('wallet') }}"> <i class="bi bi-wallet2"></i> <span>Carteira</span> </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'receivable' || Route::currentRouteName() == 'payments' ? '' : 'collapsed' }}" data-bs-target="#forms-finan" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-bank"></i><span>Meu Dinheiro</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="forms-finan" class="nav-content collapse {{ Route::currentRouteName() == 'receivable' || Route::currentRouteName() == 'payments' ? 'show' : '' }}" data-bs-parent="#sidebar-nav">  
                            <li><a href="{{ route('receivable') }}"> <i class="bi bi-circle"></i><span>Recebíveis</span> </a></li>
                            <li><a href="{{ route('payments') }}"> <i class="bi bi-circle"></i><span>Pagamentos</span> </a></li>
                        </ul>
                    </li>
                @endif

                <li class="nav-heading">Outros</li>
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-apis" data-bs-toggle="collapse" href="#">
                        <i class="bx bxl-codepen"></i><span>Integrações</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-apis" class="nav-content collapse" data-bs-parent="#sidebar-nav">  
                        <li><a href="{{ route('Integrate-wallet') }}"><i class="bi bi-circle"></i><span>Assas - Conta PF e PJ</span></a></li>
                        <li><a href="{{ route('Integrate-wallet') }}"> <i class="bi bi-circle"></i><span>Cora - Conta Digital PJ</span></a></li>
                        <li><a href="{{ route('Integrate-wallet') }}"> <i class="bi bi-circle"></i><span>Mercado Pago</span></a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'trash-users' || Route::currentRouteName() == 'trash-sales' ? '' : 'collapsed' }}" data-bs-target="#forms-trash" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-archive"></i><span>Lixeira</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-trash" class="nav-content collapse {{ Route::currentRouteName() == 'trash-users' || Route::currentRouteName() == 'trash-sales' ? 'show' : '' }}" data-bs-parent="#sidebar-nav">  
                        <li><a href="{{ route('trash-users') }}"> <i class="bi bi-circle"></i><span>Clientes</span> </a></li>
                        <li><a href="{{ route('trash-sales') }}"> <i class="bi bi-circle"></i><span>Vendas</span> </a></li>
                    </ul>
                </li>
                
                @if (Auth::user()->type == 1)
                    <li class="nav-heading">Gestão Geral</li>
                    <li class="nav-item">
                        <a class="nav-link collapsed" href="{{ route('list-lists') }}"> <i class="bi bi-list-check"></i> <span>Listas</span> </a>
                    </li>
                    
                    <li class="nav-item">
                        <li class="nav-item">
                            <a class="nav-link collapsed" href="{{ route('list-products') }}"> <i class="bi bi-box"></i> <span>Produtos</span> </a>
                        </li>
                    </li>
                
                    <li class="nav-item">
                        <a class="nav-link {{ Route::currentRouteName() == 'list-user' ? '' : 'collapsed' }}" data-bs-target="#forms-users" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-file-earmark-person"></i><span>Pessoas</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="forms-users" class="nav-content collapse {{ Route::currentRouteName() == 'list-user' ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
                            <li> <a href="{{ route('list-user', ['type' => 1]) }}"><i class="bi bi-circle"></i><span>Administradores</span></a> </li>
                            <li> <a href="{{ route('list-user', ['type' => 3]) }}"><i class="bi bi-circle"></i><span>Clientes</span></a> </li>
                            <li> <a href="{{ route('list-user', ['type' => 2]) }}"><i class="bi bi-circle"></i><span>Consultores</span></a>
                            <li> <a href="{{ route('list-user', ['type' => 4]) }}"><i class="bi bi-circle"></i><span>Consultor Interno</span></a> </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </aside>

        <main id="main" class="main">
            @yield('conteudo')
        </main>
            

        <footer id="footer" class="footer">
            <div class="copyright">
                 &copy; Copyright <strong><span>{{ env('APP_NAME') }}</span></strong>. Todos os direitos reservados 
            </div>
            <div class="credits">
                V 0.0.1
            </div>
        </footer>

        <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

        <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/js/main.js') }}"></script>
        <script src="{{ asset('assets/js/mask.js') }}"></script>
        <script>
            @if(session('error'))
                Swal.fire({
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    timer: 2000
                })
            @endif

            @if(session('info'))
                Swal.fire({
                    title: 'Atenção!',
                    text: '{{ session('info') }}',
                    icon: 'info',
                    timer: 2000
                })
            @endif
            
            @if(session('success'))
                Swal.fire({
                    title: 'Sucesso!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    timer: 2000
                })
            @endif

            document.addEventListener('DOMContentLoaded', function () {
                applyMasks(document);
                document.querySelectorAll('form.delete').forEach(form => {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Tem certeza?',
                            text: 'Você realmente deseja excluir este registro?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sim',
                            confirmButtonColor: '#008000',
                            cancelButtonText: 'Não',
                            cancelButtonColor: '#FF0000',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });

                document.querySelectorAll('input[type="file"]').forEach(input => {
                    input.addEventListener("change", function() {
                        this.closest("form").submit();
                    });
                });
            });

            $(document).on('shown.bs.modal', '.modal', function () {
                applyMasks(this);
            });

            function applyMasks(context) {
                context.querySelectorAll('.money').forEach(el => el.value && maskValue(el));
                context.querySelectorAll('.performance').forEach(el => el.value && maskPerformance(el));
                context.querySelectorAll('.phone').forEach(el => el.value && maskPhone(el));
                context.querySelectorAll('.cpfcnpj').forEach(el => el.value && maskCpfCnpj(el));
                context.querySelectorAll('.address').forEach(el => el.value && consultAddress(el));
            }

            function onClip(text) {
                navigator.clipboard.writeText(text).then(() => {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Copiado',
                        icon: 'success',
                        timer: 5000
                    });
                }).catch(err => {
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Nada copiado, tente novamente!',
                        icon: 'error',
                        timer: 5000
                    });
                });
            }
        </script>
    </body>
</html>