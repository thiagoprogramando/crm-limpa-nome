<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <title>@yield('title') - {{ env('APP_NAME') }}</title>
        <meta content="" name="description">
        <meta content="" name="keywords">

        <link href="{{ asset('assets/dashboard/img/logo.png') }}" rel="icon">

        <link href="https://fonts.gstatic.com" rel="preconnect">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

        <link href="{{ asset('assets/dashboard/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/dashboard/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/dashboard/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/dashboard/vendor/quill/quill.snow.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/dashboard/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/dashboard/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/dashboard/vendor/simple-datatables/style.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/dashboard/css/style.css') }}" rel="stylesheet">
    </head>

    <body>

        <header id="header" class="header fixed-top d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('app') }}" class="logo d-flex align-items-center">
                    <img src="assets/img/logo.png" alt="">
                    <span class="d-none d-lg-block">{{ env('APP_NAME') }}</span>
                </a>
                <i class="bi bi-list toggle-sidebar-btn"></i>
            </div>

            <div class="search-bar">
                <form class="search-form d-flex align-items-center" method="POST" action="#">
                    <input type="text" name="query" placeholder="Pesquisar" title="Pesquisar">
                    <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <nav class="header-nav ms-auto">
                <ul class="d-flex align-items-center">

                    <li class="nav-item d-block d-lg-none">
                        <a class="nav-link nav-icon search-bar-toggle " href="#"><i class="bi bi-search"></i></a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-primary badge-number">4</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                            <li class="dropdown-header"> Você possui X notificações </li>
                            <li><hr class="dropdown-divider"></li>

                            <li class="notification-item">
                                <i class="bi bi-exclamation-circle text-warning"></i>
                                <div>
                                    <h4>Lorem Ipsum</h4>
                                    <p>Quae dolorem earum veritatis oditseno</p>
                                    <p>30 min. ago</p>
                                </div>
                            </li>
                            <li> <hr class="dropdown-divider"> </li>
                            <li class="dropdown-footer"> <a href="#">Não há mais nada aqui.</a> </li>
                        </ul>
                    </li>

                    @php
                        $nameParts = explode(' ', Auth::user()->name);
                        $firstName = $nameParts[0];
                        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
                    @endphp
                    <li class="nav-item dropdown pe-3">
                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                            <span class="d-none d-md-block dropdown-toggle ps-2">{{ $firstName }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                            <li class="dropdown-header">
                                <h6>{{ $firstName }} {{ $lastName }}</h6>
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
                    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-briefcase"></i><span>Negócios & Produtos</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                        @foreach($business as $busines)
                            <li><a href="{{ route('createsale', ['id' => $busines->id]) }}"> <i class="bi bi-circle"></i><span>{{ $busines->name }}</span> </a></li>
                        @endforeach
                    </ul>
                </li>

                <li class="nav-heading">Financeiro</li>

                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('wallet') }}"> <i class="bi bi-wallet2"></i> <span>Carteira</span> </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('receivable') }}"> <i class="bi bi-box-arrow-in-up"></i> <span>Recebíveis</span> </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('payments') }}"> <i class="bi bi-box-arrow-down"></i> <span>Pagamentos</span> </a>
                </li>

                <li class="nav-heading">Gestão</li>

                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-sale" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bag"></i><span>Vendas</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-sale" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                        <li>
                            <a href="{{ route('manager-sale') }}"> <i class="bi bi-circle"></i><span>Vendas</span> </a>
                        </li>
                        <li>
                            <a href="{{ route('invoice-default') }}"> <i class="bi bi-circle"></i><span>Inadimplência</span> </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-list" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-list-check"></i><span>Lista</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-list" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                        <li>
                            <a href="{{ route('lists') }}"> <i class="bi bi-circle"></i><span>Listas</span> </a>
                        </li>
                        @if (Auth::user()->type == 1)
                            <li>
                                <a href="{{ route('createlist') }}"> <i class="bi bi-circle"></i><span>Criar Lista</span> </a>
                            </li>
                        @endif
                    </ul>
                </li>

                @if (Auth::user()->type == 1)
                    <li class="nav-item">
                        <a class="nav-link collapsed" data-bs-target="#forms-product" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-box"></i><span>Produtos</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="forms-product" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                            <li>
                                <a href="{{ route('listproduct') }}"> <i class="bi bi-circle"></i><span>Produtos</span> </a>
                            </li>
                            <li>
                                <a href="{{ route('createproduct') }}"> <i class="bi bi-circle"></i><span>Criar Produto</span> </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>

        </aside>

        <main id="main" class="main">
            @yield('conteudo')
        </main>

        <footer id="footer" class="footer">
            <div class="copyright"> &copy; Copyright <strong><span>{{ env('APP_NAME') }}</span></strong>. Todos os direitos reservados </div>
            <div class="credits">
                Desenvolvido por <a href="https://ifuture.cloud/">Ifuture.cloud</a>
            </div>
        </footer>

        <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

        <script src="{{ asset('assets/dashboard/vendor/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('assets/dashboard/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/dashboard/vendor/chart.js/chart.umd.js') }}"></script>
        <script src="{{ asset('assets/dashboard/vendor/echarts/echarts.min.js') }}"></script>
        <script src="{{ asset('assets/dashboard/vendor/quill/quill.min.js') }}"></script>
        <script src="{{ asset('assets/dashboard/vendor/simple-datatables/simple-datatables.js') }}"></script>
        <script src="{{ asset('assets/dashboard/vendor/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ asset('assets/dashboard/vendor/php-email-form/validate.js') }}"></script>
        <script src="{{ asset('assets/dashboard/js/main.js') }}"></script>
        <script src="{{ asset('assets/dashboard/js/jquery.js') }}"></script>
        <script src="{{ asset('assets/dashboard/js/mask.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            @if(session('error'))
                Swal.fire({
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    icon: 'error',
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
                
                const deleteForms = document.querySelectorAll('form.delete');
                deleteForms.forEach(form => {
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

                let inputs = document.querySelectorAll('input[type="text"][oninput="mascaraReal(this)"]');
                inputs.forEach(function(input) {
                    mascaraReal(input);
                });
            });
        </script>

    </body>
</html>