<nav class="main-header navbar navbar-expand-md navbar-dark">
    <style>
        /* Navbar compacto para que todos los items quepan en una sola fila */
        .main-header .navbar-nav .nav-link {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.6rem !important;
            letter-spacing: 0.2px;
        }
        .main-header .navbar-nav .nav-link i {
            font-size: 0.75rem;
        }
        @media (max-width: 991.98px) {
            .main-header .navbar-nav .nav-link {
                font-size: 0.95rem !important;
                padding: 0.75rem 0.9rem !important;
            }
            .main-header .navbar-nav .nav-link i {
                font-size: 0.9rem;
            }
        }
    </style>
    <div class="container-fluid">
        <!-- Brand -->
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <img src="{{ asset(Setting::getValue('app_logo')) }}" alt="{{ Setting::getName('app_name') }}" class="brand-image img-circle elevation-3 brand-glow" style="opacity: .8">
            <span class="brand-text font-weight-bold ml-2">{{ Setting::getValue('app_name') }}</span>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav ml-auto mr-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active':'' }}">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                </li>

                <!-- Clientes -->
                <li class="nav-item">
                    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers*') ? 'active':'' }}">
                        <i class="fas fa-users mr-1"></i> Clientes
                    </a>
                </li>

                <!-- Inventario Dropdown -->
                <li class="nav-item dropdown">
                    <a id="dropdownInventory" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('products*') || request()->routeIs('categories*') ? 'active':'' }}">
                        <i class="fas fa-box mr-1"></i> Inventario
                    </a>
                    <ul aria-labelledby="dropdownInventory" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('categories.index') }}" class="dropdown-item">Categorías</a></li>
                        <li><a href="{{ route('brands.index') }}" class="dropdown-item">Marcas</a></li>
                        <li><a href="{{ route('products.index') }}" class="dropdown-item">Productos</a></li>
                        <li><a href="{{ route('products.expiring') }}" class="dropdown-item">Por Vencer</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('stock.adjustments.index') }}" class="dropdown-item text-warning font-weight-bold"><i class="fas fa-sliders-h mr-1"></i>Ajuste de Stock</a></li>
                    </ul>
                </li>

                <!-- Ventas Dropdown -->
                <li class="nav-item dropdown">
                    <a id="dropdownSales" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('sales*') || request()->routeIs('customers*') ? 'active':'' }}">
                        <i class="fas fa-shopping-cart mr-1"></i> Ventas
                    </a>
                    <ul aria-labelledby="dropdownSales" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('sales.pos') }}" class="dropdown-item"><i class="fas fa-cash-register text-success mr-2"></i>Nueva Venta</a></li>
                        <li><a href="{{ route('sales.index') }}" class="dropdown-item"><i class="fas fa-list text-muted mr-2"></i>Historial de Ventas</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('customers.index') }}" class="dropdown-item"><i class="fas fa-users text-info mr-2"></i>Clientes</a></li>
                    </ul>
                </li>

                <!-- Compras Dropdown -->
                <li class="nav-item dropdown">
                    <a id="dropdownPurchases" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('purchases*') || request()->routeIs('suppliers*') ? 'active':'' }}">
                        <i class="fas fa-shopping-bag mr-1"></i> Compras
                    </a>
                    <ul aria-labelledby="dropdownPurchases" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('purchases.create') }}" class="dropdown-item">Nueva Compra</a></li>
                        <li><a href="{{ route('purchases.index') }}" class="dropdown-item">Historial</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('suppliers.index') }}" class="dropdown-item">Proveedores</a></li>
                    </ul>
                </li>

                <!-- Finanzas Dropdown -->
                <li class="nav-item dropdown">
                    <a id="dropdownFinances" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('financials*') ? 'active':'' }}">
                        <i class="fas fa-money-bill-wave mr-1"></i> Finanzas
                    </a>
                    <ul aria-labelledby="dropdownFinances" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('financials.cajas.index') }}" class="dropdown-item">Cajas</a></li>
                        <li><a href="{{ route('financials.expenses.index') }}" class="dropdown-item">Gastos</a></li>
                    </ul>
                </li>

                <!-- Créditos Dropdown -->
                <li class="nav-item dropdown">
                    <a id="dropdownCredits" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('credits*') ? 'active':'' }}">
                        <i class="fas fa-hand-holding-usd mr-1"></i> Créditos
                    </a>
                    <ul aria-labelledby="dropdownCredits" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('credits.receivables') }}" class="dropdown-item">Por Cobrar</a></li>
                        <li><a href="{{ route('credits.payables') }}" class="dropdown-item">Por Pagar</a></li>
                    </ul>
                </li>

                <!-- Reportes Dropdown -->
                <li class="nav-item dropdown">
                    <a id="dropdownReports" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('reports*') ? 'active':'' }}">
                        <i class="fas fa-file-pdf mr-1"></i> Reportes
                    </a>
                    <ul aria-labelledby="dropdownReports" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('reports.index') }}" class="dropdown-item">Generar Reportes</a></li>
                    </ul>
                </li>

                <!-- Ajustes Dropdown -->
                <li class="nav-item dropdown">
                    <a id="dropdownSettings" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->routeIs('user*') || request()->routeIs('role*') || request()->routeIs('permission*') || request()->routeIs('setting*') || request()->routeIs('module*') ? 'active':'' }}">
                        <i class="fas fa-cog mr-1"></i> Ajustes
                    </a>
                    <ul aria-labelledby="dropdownSettings" class="dropdown-menu border-0 shadow">
                        @can('read user')
                        <li><a href="{{ route('user.index') }}" class="dropdown-item">Usuarios</a></li>
                        @endcan
                        @can('read role')
                        <li><a href="{{ route('role.index') }}" class="dropdown-item">Roles</a></li>
                        @endcan
                        @can('read permission')
                        <li><a href="{{ route('permission.index') }}" class="dropdown-item">Permisos</a></li>
                        @endcan
                        <li class="dropdown-divider"></li>
                        @can('read setting')
                        <li><a href="{{ route('setting.index') }}" class="dropdown-item">Configuración</a></li>
                        @endcan
                        @can('filemanager')
                        <li><a href="{{ route('filemanager') }}" class="dropdown-item">Gestor Archivos</a></li>
                        @endcan
                        @can('read module')
                        <li><a href="{{ route('module.index') }}" class="dropdown-item">Módulos Dinámicos</a></li>
                        @endcan
                    </ul>
                </li>

                <!-- Dynamic Modules -->
                @foreach ($modulemenus as $menus)
                    @if ($menus['menu_count'] == 1)
                        @foreach ($menus['menus'] as $menu)
                            @can($menu['permission'])
                                <li class="nav-item">
                                    <a href="{{ route($menu['route']) }}" class="nav-link {{ request()->routeIs($menu['route']) == strtolower($menu['name']) ? 'active':'' }}">
                                        {!! $menu['icon'] !!} {{ $menu['name'] }}
                                    </a>
                                </li>
                            @endcan
                        @endforeach
                    @elseif ($menus['menu_count'] > 1)
                        <li class="nav-item dropdown">
                            <a id="dropdown-{{ $menus['module'] }}" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">{{ strtoupper($menus['module']) }}</a>
                            <ul aria-labelledby="dropdown-{{ $menus['module'] }}" class="dropdown-menu border-0 shadow">
                                @foreach ($menus['menus'] as $menu)
                                    @can($menu['permission'])
                                    <li><a href="{{ route($menu['route']) }}" class="dropdown-item">{!! $menu['icon'] !!} {{ $menu['name'] }}</a></li>
                                    @endcan
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <!-- Right navbar links (Profile, Theme, etc) -->
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            <li class="nav-item">
                <a class="nav-link" id="btntheme" role="button">
                    <i id="icontheme" class="fas fa-sun"></i>
                </a>
            </li>
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="{{ asset('template/admin/dist/img/user2-160x160.jpg') }}" class="user-image img-circle elevation-2" alt="User Image">
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow border-0">
                    <li class="user-header bg-primary">
                        <img src="{{ asset('template/admin/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                        <p>
                            {{ Auth::user()->name }} - {{ implode(",", Auth::user()->getRoleNames()->toArray()) }}
                            <small>Última actualización {{ date('d-m-Y H:i', strtotime(Auth::user()->updated_at)) }}</small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <a href="{{ route('index') }}" target="_blank" class="btn btn-default btn-flat">Ver Web</a>
                        <a href="#" data-toggle="modal" data-target="#modal-logout" data-backdrop="static" data-keyboard="false" class="btn btn-danger btn-flat float-right">Cerrar Sesión</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
