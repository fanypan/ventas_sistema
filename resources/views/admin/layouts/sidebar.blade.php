<aside class="main-sidebar sidebar-dark-primary elevation-4 premium-sidebar">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
    <img src="{{ asset(Setting::getValue('app_logo')) }}" alt="{{ Setting::getName('app_name') }}" class="brand-image img-circle elevation-3 brand-glow" style="opacity: .8">
    <span class="brand-text font-weight-bold">{{ Setting::getValue('app_name') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
            <img src="{{ asset('template/admin/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
            <a href="#" class="d-block font-weight-bold text-white">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active':'' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>@php $i = 1; @endphp
                @foreach ($modulemenus as $menus)
                    @if ($menus['menu_count'] == 1)
                        @foreach ($menus['menus'] as $menu)
                            @if ($i == 1)
                                @php
                                    $perm[] = $menu['permission'];
                                @endphp
                                @canany($perm)
                                    <li class="nav-header ml-2">DATOS MAESTROS</li>
                                @endcanany
                            @endif
                            @can($menu['permission'])
                                <li class="nav-item">
                                    <a href="{{ route($menu['route']) }}" class="nav-link {{ request()->routeIs($menu['route']) == strtolower($menu['name']) ? 'active':'' }}">
                                        <i class="nav-icon {{ $menu['icon'] }}"></i>
                                        <p>{{ $menu['name'] }}</p>
                                    </a>
                                </li>
                            @endcan
                            @php $i++; @endphp
                        @endforeach
                    @endif
                @endforeach
                @foreach ($modulemenus as $menus)
                    @if ($menus['menu_count'] > 1)
                        @foreach ($menus['menus'] as $menu)
                            @if (count($menus['menus']) > 1)
                                @if ($loop->iteration == 1)
                                    @php
                                        $perm[] = $menu['permission'];
                                    @endphp
                                    @canany($perm)
                                        <li class="nav-header ml-2">{{ strtoupper($menus['module']) }}</li>
                                    @endcanany
                                @endif
                                @can($menu['permission'])
                                    <li class="nav-item">
                                        <a href="{{ route($menu['route']) }}" class="nav-link {{ request()->routeIs($menu['route']) == strtolower($menu['name']) ? 'active':'' }}">
                                            <i class="nav-icon {{ $menu['icon'] }}"></i>
                                            <p>{{ $menu['name'] }}</p>
                                        </a>
                                    </li>
                                @endcan
                            @endif
                        @endforeach
                    @endif
                @endforeach
                <li class="nav-header ml-2">SISTEMA VENTAS</li>
                @can('read customer')
                <li class="nav-item">
                    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers*') ? 'active':'' }}">
                        <i class="fas fa-users nav-icon"></i>
                        <p>Clientes</p>
                    </a>
                </li>
                @endcan
                @canany(['read product', 'read category', 'read brand', 'read stock'])
                <li class="nav-item {{ request()->routeIs('products*') || request()->routeIs('categories*') || request()->routeIs('brands*') || request()->routeIs('stock.adjustments*') ? 'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('products*') || request()->routeIs('categories*') || request()->routeIs('brands*') || request()->routeIs('stock.adjustments*') ? 'active':'' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p> Inventario <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('read category')
                        <li class="nav-item">
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Categorías</p>
                            </a>
                        </li>
                        @endcan
                        @can('read brand')
                        <li class="nav-item">
                            <a href="{{ route('brands.index') }}" class="nav-link {{ request()->routeIs('brands*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Marcas</p>
                            </a>
                        </li>
                        @endcan
                        @can('read product')
                        <li class="nav-item">
                            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Productos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('products.expiring') }}" class="nav-link {{ request()->routeIs('products.expiring') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Por Vencer</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('products.zero') }}" class="nav-link {{ request()->routeIs('products.zero') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stock 0</p>
                            </a>
                        </li>
                        @endcan
                        @can('read stock')
                        <li class="nav-item">
                            <a href="{{ route('stock.adjustments.index') }}" class="nav-link {{ request()->routeIs('stock.adjustments*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Ajuste de stock</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['read sale', 'create sale'])
                <li class="nav-item {{ request()->routeIs('sales*') ? 'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('sales*') ? 'active':'' }}">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p> Ventas <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('create sale')
                        <li class="nav-item">
                            <a href="{{ route('sales.pos') }}" class="nav-link {{ request()->routeIs('sales.pos') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nueva Venta</p>
                            </a>
                        </li>
                        @endcan
                        @can('read sale')
                        <li class="nav-item">
                            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Historial</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['read purchase', 'create purchase', 'read supplier'])
                <li class="nav-item {{ request()->routeIs('purchases*') || request()->routeIs('suppliers*') ? 'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('purchases*') || request()->routeIs('suppliers*') ? 'active':'' }}">
                        <i class="nav-icon fas fa-shopping-bag"></i>
                        <p> Compras <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('create purchase')
                        <li class="nav-item">
                            <a href="{{ route('purchases.create') }}" class="nav-link {{ request()->routeIs('purchases.create') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nueva Compra</p>
                            </a>
                        </li>
                        @endcan
                        @can('read purchase')
                        <li class="nav-item">
                            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.index') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Historial</p>
                            </a>
                        </li>
                        @endcan
                        @can('read supplier')
                        <li class="nav-item">
                            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Proveedores</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['read cash', 'read expense', 'consume insumo'])
                <li class="nav-item {{ request()->routeIs('financials*') ? 'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('financials*') ? 'active':'' }}">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p> Finanzas <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('read cash')
                        <li class="nav-item">
                            <a href="{{ route('financials.cajas.index') }}" class="nav-link {{ request()->routeIs('financials.cajas*') && !request()->routeIs('financials.cajas.history') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cajas</p>
                            </a>
                        </li>
                        @endcan
                        @can('read expense')
                        <li class="nav-item">
                            <a href="{{ route('financials.expenses.index') }}" class="nav-link {{ request()->routeIs('financials.expenses*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Gastos</p>
                            </a>
                        </li>
                        @endcan
                        @can('consume insumo')
                        <li class="nav-item">
                            <a href="{{ route('financials.insumos.consume') }}" class="nav-link {{ request()->routeIs('financials.insumos*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Consumo de insumos</p>
                            </a>
                        </li>
                        @endcan
                        @can('read cash')
                        <li class="nav-item">
                            <a href="{{ route('financials.cajas.history') }}" class="nav-link {{ request()->routeIs('financials.cajas.history') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Histórico de arqueos</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @canany(['read credit', 'read supplier'])
                <li class="nav-item {{ request()->routeIs('credits*') ? 'menu-open':'' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('credits*') ? 'active':'' }}">
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p> Créditos <i class="right fas fa-angle-left"></i> </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('read credit')
                        <li class="nav-item">
                            <a href="{{ route('credits.receivables') }}" class="nav-link {{ request()->routeIs('credits.receivables') || request()->routeIs('credits.kardex.customer*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cuentas por Cobrar</p>
                            </a>
                        </li>
                        @endcan
                        @can('read supplier')
                        <li class="nav-item">
                            <a href="{{ route('credits.payables') }}" class="nav-link {{ request()->routeIs('credits.payables') || request()->routeIs('credits.kardex.supplier*') ? 'active':'' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cuentas por Pagar</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @can('read report')
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports*') ? 'active':'' }}">
                        <i class="nav-icon fas fa-file-pdf"></i>
                        <p>Reportes</p>
                    </a>
                </li>
                @endcan
                <li class="nav-header ml-2">SUSCRIPCIÓN</li>
                <li class="nav-item">
                    <a href="{{ route('tenant.plan') }}" class="nav-link {{ request()->routeIs('tenant.plan') ? 'active':'' }}">
                        <i class="fas fa-receipt nav-icon"></i>
                        <p>Mi plan</p>
                    </a>
                </li>
                <li class="nav-header ml-2">ACCESS</li>
                @can('read user')
                    <li class="nav-item">
                        <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active':'' }}">
                            <i class="fas fa-user nav-icon"></i>
                            <p>Usuarios</p>
                        </a>
                    </li>
                @endcan
                @can('read role')
                    <li class="nav-item">
                        <a href="{{ route('role.index') }}" class="nav-link {{ request()->routeIs('role.index') ? 'active':'' }}">
                            <i class="fas fa-user-cog nav-icon"></i>
                            <p>Roles</p>
                        </a>
                    </li>
                @endcan
                @can('read permission')
                    <li class="nav-item">
                        <a href="{{ route('permission.index') }}" class="nav-link {{ request()->routeIs('permission.index') ? 'active':'' }}">
                            <i class="fas fa-unlock nav-icon"></i>
                            <p>Permisos</p>
                        </a>
                    </li>
                @endcan
                <li class="nav-header ml-2">SETTINGS</li>
                @can('read setting')
                    <li class="nav-item">
                        <a href="{{ route('setting.index') }}" class="nav-link {{ request()->routeIs('setting.index') ? 'active':'' }}">
                            <i class="fas fa-cog nav-icon"></i>
                            <p>Configuración</p>
                        </a>
                    </li>
                @endcan
                @can('filemanager')
                    <li class="nav-item">
                        <a href="{{ route('filemanager') }}" class="nav-link {{ request()->routeIs('filemanager') ? 'active':'' }}">
                            <i class="nav-icon fas fa-folder"></i>
                            <p>Gestor de Archivos</p>
                        </a>
                    </li>
                @endcan
                @can('read module')
                    <li class="nav-item">
                        <a href="{{ route('module.index') }}" class="nav-link {{ request()->routeIs('module.index') ? 'active':'' }}">
                            <i class="fas fa-network-wired nav-icon"></i>
                            <p>Módulos</p>
                        </a>
                    </li>
                @endcan
                <li class="nav-header"></li>
                <li class="nav-item">
                <a href="#" class="nav-link bg-danger" data-toggle="modal" data-target="#modal-logout" data-backdrop="static" data-keyboard="false">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <p>CERRAR SESIÓN</p>
                </a>
                </li>
                <li class="nav-header"></li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>