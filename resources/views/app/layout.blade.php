<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <title>@yield('title') - {{ env('APP_NAME') }}</title>
        <meta content="" name="description">
        <meta content="" name="keywords">

        <link href="{{ asset('assets/dashboard/img/favicon.png') }}" rel="icon">

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

        <script src="{{ asset('assets/dashboard/js/chart.js')}}"></script>
        <script src="{{ asset('assets/dashboard/js/sweetalert.js')}}"></script>
        <script src="{{ asset('assets/dashboard/js/jquery.js') }}"></script>
    </head>

    <body>

        <header id="header" class="header fixed-top d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('app') }}" class="logo d-flex align-items-center">
                    <img src="{{ asset('assets/dashboard/img/logo.png') }}">
                    {{-- <span class="d-none d-lg-block">{{ env('APP_NAME') }}</span> --}}
                </a>
                <i class="bi bi-list toggle-sidebar-btn"></i>
            </div>

            <div class="search-bar">
                <form class="search-form d-flex align-items-center" method="GET" action="{{ route('search') }}">
                    <input type="text" name="search" placeholder="Pesquisar" title="Pesquisar">
                    <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <nav class="header-nav ms-auto">
                <ul class="d-flex align-items-center">

                    <li class="nav-item d-block d-lg-none">
                        <a class="nav-link nav-icon search-bar-toggle " href="#"><i class="bi bi-search"></i></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-icon" href="#">
                            <i class="bi bi-alarm"></i> 
                            <span>{{ Auth::user()->timeMonthly() }}</span>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-primary badge-number">{{ $totalNotification }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                            <li class="dropdown-header"> Você possui {{ $totalNotification }} notificações </li>
                            <li><hr class="dropdown-divider"></li>

                            @foreach ($notifications as $notification)
                                <a href="{{ route('view-notification', ['id' => $notification->id]) }}">
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

                    @php
                        $nameParts = explode(' ', Auth::user()->name);
                        $firstName = $nameParts[0];
                        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
                    @endphp
                    <li class="nav-item dropdown pe-3">
                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                            <span class="dropdown-toggle ps-2">{{ $firstName }}</span>
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
                
                {{-- <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('apresentation') }}"> <i class="bi bi-book"></i> <span>Material de apoio</span> </a>
                </li> --}}

                <li class="nav-item">
                    <a class="nav-link collapsed" href="https://servicos.ehmconsultas.com/index.php" target="_blank"> <i class="bi bi-search"></i> <span>Consultas</span> </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-briefcase"></i><span>Enviar Contrato (Cliente)</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                        @foreach($business as $busines)
                            <li><a href="@if(Auth::user()->type == 7 || Auth::user()->type == 9) {{ route('createupload', ['id' => $busines->id]) }} @else {{ route('createsale', ['id' => $busines->id]) }} @endif"> <i class="bi bi-circle"></i><span>{{ $busines->name }}</span> </a></li>
                        @endforeach
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-upload" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-cloud-upload"></i><span>Enviar Nome (Associação)</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-upload" class="nav-content collapse " data-bs-parent="#sidebar-upload">
                        @foreach($business as $busines)
                            <li><a href="{{ route('createupload', ['id' => $busines->id]) }}"> <i class="bi bi-circle"></i><span>{{ $busines->name }}</span> </a></li>
                        @endforeach
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-sale" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bag"></i><span>Vendas</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-sale" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                        <li> <a href="{{ route('manager-sale') }}"> <i class="bi bi-circle"></i><span>Vendas</span> </a> </li>
                        <li> <a href="{{ route('invoice-default') }}"> <i class="bi bi-circle"></i><span>Inadimplência</span> </a> </li>
                        @if (Auth::user()->type == 1)
                            <li><a href="{{ route('coupons') }}"> <i class="bi bi-circle"></i><span>Cupons</span> </a></li>
                        @endif
                    </ul>
                </li>
                
                <li class="nav-heading">Pessoas</li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('list-network') }}"> <i class="bi bi-person-lines-fill"></i> <span>Minha Rede</span> </a>
                </li>
           
                <li class="nav-item">
                    <a class="nav-link collapsed" href="{{ route('list-client') }}"> <i class="bi bi-file-earmark-person"></i> <span>Clientes</span> </a>
                </li>

                <li class="nav-heading">Customização</li>
                <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-target="#forms-integration" data-bs-toggle="collapse" href="#">
                        <i class="ri-braces-line"></i><span>Integrações</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="forms-integration" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                        @if (Auth::user()->white_label_contract == 1)
                            <li> 
                                <a href="{{ route('profile-white-label') }}"><i class="bi bi-circle"></i><span>Contrato</span></a>
                            </li>
                        @endif
                        {{-- <li> <a href=""> <i class="bi bi-circle"></i><span>WhatsApp</span> </a> </li> --}}
                    </ul>
                </li>

                @if (Auth::user()->type != 6 && !empty(Auth::user()->api_key) && !empty(Auth::user()->wallet))
                    <li class="nav-heading">Financeiro</li>

                    <li class="nav-item">
                        <a class="nav-link collapsed" href="{{ route('wallet') }}"> <i class="bi bi-wallet2"></i> <span>Carteira</span> </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link collapsed" data-bs-target="#forms-finan" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-bank"></i><span>Operações</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="forms-finan" class="nav-content collapse " data-bs-parent="#sidebar-nav">  
                            {{-- <li><a href="{{ route('withdraw') }}"> <i class="bi bi-circle"></i><span>Saques</span> </a></li> --}}
                            <li><a href="{{ route('receivable') }}"> <i class="bi bi-circle"></i><span>Recebíveis</span> </a></li>
                            <li><a href="{{ route('payments') }}"> <i class="bi bi-circle"></i><span>Pagamentos</span> </a></li>
                        </ul>
                    </li>
                @endif

                
                @if (Auth::user()->type == 1)
                <li class="nav-heading">Gestão</li>
                    <li class="nav-item">
                        <a class="nav-link collapsed" href="{{ route('lists') }}"> <i class="bi bi-list-check"></i> <span>Listas</span> </a>
                    </li>
                    
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
                
                    <li class="nav-item">
                        <a class="nav-link collapsed" data-bs-target="#forms-users" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-file-earmark-person"></i><span>Pessoas</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="forms-users" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                            <li> <a href="{{ route('list-user', ['type' => 1]) }}"><i class="bi bi-circle"></i><span>Administradores</span></a> </li>
                            <li> <a href="{{ route('list-user', ['type' => 3]) }}"><i class="bi bi-circle"></i><span>Clientes</span></a> </li>
                            <li> <a href="{{ route('list-user', ['type' => 2]) }}"><i class="bi bi-circle"></i><span>Consultores</span></a>
                            <li> <a href="{{ route('list-user', ['type' => 4]) }}"><i class="bi bi-circle"></i><span>Consultor Interno</span></a> </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link collapsed" data-bs-target="#forms-actvie" data-bs-toggle="collapse" href="#">
                            <i class="bi bi-person-bounding-box"></i><span>Atividade</span><i class="bi bi-chevron-down ms-auto"></i>
                        </a>
                        <ul id="forms-actvie" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                            <li> <a href="{{ route('list-active', ['status' => 1]) }}"><i class="bi bi-circle"></i><span>Ativos</span></a> </li>
                            <li> <a href="{{ route('list-active', ['status' => 2]) }}"><i class="bi bi-circle"></i><span>Inativos</span></a> </li>
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

        <script src="{{ asset('assets/dashboard/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/dashboard/js/main.js') }}"></script>
        <script src="{{ asset('assets/dashboard/js/mask.js') }}"></script>
        <script src="{{ asset('assets/dashboard/js/excel.js') }}"></script>
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
                
                var links = document.querySelectorAll('.confirm');
                links.forEach(function(link) {
                    link.addEventListener('click', function(event) {

                        event.preventDefault();
                        var message = this.getAttribute('data-message') || 'Tem certeza?';
                        
                        Swal.fire({
                            title: 'Tem certeza?',
                            text: 'Você realmente deseja executar esta ação?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sim',
                            confirmButtonColor: '#008000',
                            cancelButtonText: 'Não',
                            cancelButtonColor: '#FF0000',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = link.href;
                            }
                        });
                    });
                });

                let inputs = document.querySelectorAll('input[type="text"][oninput="mascaraReal(this)"]');
                inputs.forEach(function(input) {
                    mascaraReal(input);
                });
            });

            $('#gerarExcel').click(function() {

                var tabela = document.getElementById('table');
                var wb = XLSX.utils.table_to_book(tabela, {
                    sheet: 'Sheet 1'
                });
                var wbout = XLSX.write(wb, {
                    bookType: 'xlsx',
                    type: 'binary'
                });

                function s2ab(s) {
                    var buf = new ArrayBuffer(s.length);
                    var view = new Uint8Array(buf);
                    for (var i = 0; i < s.length; i++) {
                        view[i] = s.charCodeAt(i) & 0xFF;
                    }
                    return buf;
                }

                var blob = new Blob([s2ab(wbout)], {
                    type: 'application/octet-stream'
                });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'tabela.xlsx';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                setTimeout(function() {
                    URL.revokeObjectURL(url);
                }, 100);
            });
        </script>
    </body>
</html>