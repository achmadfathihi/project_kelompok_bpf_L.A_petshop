<nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
    <div class="container-fluid">
        <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="{{ route('home') }}">
            @yield('title', 'Dashboard')
        </a>

        <ul class="navbar-nav align-items-center d-none d-md-flex ml-auto">
            @include('layouts.admin.bell')
            <li class="nav-item dropdown">
                <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="media align-items-center">
                        <span class="avatar avatar-sm rounded-circle bg-white text-primary"><i class="ni ni-single-02"></i></span>
                        <div class="media-body ml-2 d-none d-lg-block">
                            <span class="mb-0 text-sm font-weight-bold text-white">{{ auth()->user()->name }}</span>
                            <span class="badge badge-pill bg-white text-primary ml-1">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ auth()->user()->email }}</h6>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="ni ni-user-run"></i><span>Keluar</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>