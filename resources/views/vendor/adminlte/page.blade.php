@extends('adminlte::master')

@section('adminlte_css')
  <link rel="stylesheet"
  href="{{ asset('vendor/adminlte/dist/css/skins/skin-' . config('adminlte.skin', 'blue') . '.min.css')}} ">
  @stack('css')
  @yield('css')
@stop

@section('body_class', 'skin-' . config('adminlte.skin', 'blue') . ' sidebar-mini ' . (config('adminlte.layout') ? [
  'boxed' => 'layout-boxed',
  'fixed' => 'fixed',
  'top-nav' => 'layout-top-nav'
  ][config('adminlte.layout')] : '') . (config('adminlte.collapse_sidebar') ? ' sidebar-collapse ' : ''))

  @section('body')
    <div class="wrapper">
      @if(session()->has('notif'))
        <div class="utomodeck alert alert-{{ session()->get('notif')['code'] === 'success' ? 'success' : 'danger' }} alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <h4><i class="icon fa fa-{{ session()->get('notif')['code'] === 'success' ? 'check' : 'close' }}"></i> {{ session()->get('notif')['code'] }}!</h4>
          {{ session()->get('notif')['message'] }}
        </div>
        @php session()->forget('notif') @endphp
      @endif

      <!-- Main Header -->
      <header class="utomodeck main-header">
        @if(config('adminlte.layout') == 'top-nav')
          <nav class="navbar navbar-static-top">
            <div class="container">
              <div class="navbar-header">
                <a href="{{ url(config('adminlte.dashboard_url', 'home')) }}" class="navbar-brand">
                  {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
                </a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                  <i class="fa fa-bars"></i>
                </button>
              </div>

              <!-- Collect the nav links, forms, and other content for toggling -->
              <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                <ul class="nav navbar-nav">
                  @each('adminlte::partials.menu-item-top-nav', $adminlte->menu(), 'item')
                </ul>
              </div>
              <!-- /.navbar-collapse -->
            @else
              <!-- Logo -->
              <a href="{{ url(config('adminlte.dashboard_url', 'home')) }}" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini">{!! config('adminlte.logo_mini', '<b>A</b>LT') !!}</span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg">{!! config('adminlte.logo', '<b>Admin</b>LTE') !!}</span>
              </a>

              <!-- Header Navbar -->
              <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                  <span class="sr-only">{{ trans('adminlte::adminlte.toggle_navigation') }}</span>
                </a>
              @endif
              <!-- Navbar Right Menu -->
              <div class="navbar-custom-menu">

                <ul class="nav navbar-nav">
                  <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <img src="{{ asset('img/no-image.png') }}" class="user-image" alt="User Image">
                      <span class="hidden-xs">{{ ucwords(Auth::user()->name) }}</span>
                    </a>
                    <ul class="dropdown-menu">
                      <!-- User image -->
                      <li class="user-header">
                        <img src="{{ asset('img/no-image.png') }}" class="img-circle" alt="User Image">

                        <p>
                          {{ ucwords(Auth::user()->name) }}
                          <small>Administrator</small>
                        </p>
                      </li>
                      <!-- Menu Footer-->
                      <li class="user-footer">
                        <div class="pull-left">
                          <a href="#" class="btn btn-default btn-flat">Profile</a>
                        </div>
                        <div class="pull-right">
                          @if(config('adminlte.logout_method') == 'GET' || !config('adminlte.logout_method') && version_compare(\Illuminate\Foundation\Application::VERSION, '5.3.0', '<'))
                            <a class="btn btn-default btn-flat" href="{{ url(config('adminlte.logout_url', 'auth/logout')) }}">
                              <i class="fa fa-fw fa-power-off"></i> {{ trans('adminlte::adminlte.log_out') }}
                            </a>
                          @else
                            <a class="btn btn-default btn-flat" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            >
                            <i class="fa fa-fw fa-power-off"></i> {{ trans('adminlte::adminlte.log_out') }}
                          </a>
                          <form id="logout-form" action="{{ url(config('adminlte.logout_url', 'auth/logout')) }}" method="POST" style="display: none;">
                            @if(config('adminlte.logout_method'))
                              {{ method_field(config('adminlte.logout_method')) }}
                            @endif
                            {{ csrf_field() }}
                          </form>
                        @endif
                      </div>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
            @if(config('adminlte.layout') == 'top-nav')
            </div>
          @endif
        </nav>
      </header>

      @if(config('adminlte.layout') != 'top-nav')
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">

          <!-- sidebar: style can be found in sidebar.less -->
          <section class="sidebar">

            <!-- Sidebar Menu -->
            <ul class="sidebar-menu" data-widget="tree">
              @each('adminlte::partials.menu-item', $adminlte->menu(), 'item')
            </ul>
            <!-- /.sidebar-menu -->
          </section>
          <!-- /.sidebar -->
        </aside>
      @endif

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        @if(config('adminlte.layout') == 'top-nav')
          <div class="container">
          @endif

          <!-- Content Header (Page header) -->
          <section class="content-header">
            @yield('content_header')
            @if(!empty($route))
              <?php $breadcrumbs = explode('/', $route); ?>
              <?php $lastBreadcrumb = array_pop($breadcrumbs); ?>
              <ol class="breadcrumb">
                <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> Home</a></li>

                <?php $urlBreadcrumb = []; ?>
                @foreach($breadcrumbs as $breadcrumb)
                  @if($breadcrumb != 'home')
                    <?php $urlBreadcrumb[] = $breadcrumb; ?>
                    <li><a href="{{ url( implode('/', $urlBreadcrumb) ) }}">{{ $breadcrumb }}</a></li>
                  @endif
                @endforeach

                <li class="active">{{ $lastBreadcrumb }}</li>
              </ol>
            @endif
          </section>

          <!-- Main content -->
          <section class="content">

          @if (($message = Session::get('success')) || $message = Session::get('error'))
            <div class="alert alert-{{ Session::get('success') ? 'success' : 'danger' }} alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              {!! $message !!}
            </div>
          @endif

            @yield('content')

          </section>
          <!-- /.content -->
          @if(config('adminlte.layout') == 'top-nav')
          </div>
          <!-- /.container -->
        @endif
      </div>
      <!-- /.content-wrapper -->

    </div>
    <!-- ./wrapper -->
  @stop

  @section('adminlte_js')
    <script src="{{ asset('vendor/jquery-number/jquery.number.js')}}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @stack('js')
    @yield('js')
  @stop
