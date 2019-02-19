<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Área de administração do site Filmes 100 ads">
    <meta name="author" content="Isaac Angello">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Filmes sem ADS - ADM</title>

    <!-- Bootstrap core CSS-->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">


    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Page level plugin CSS-->
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.css') }}" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin.css') }}" rel="stylesheet">

    <!-- regras adicionais -->
    <link href="{{ asset('css/append.css') }}" rel="stylesheet">
</head>
<body>
<!--
<nav class="navbar navbar-expand navbar-dark bg-dark static-top">
    <div style="color: whitesmoke;" class="container-fluid nav-justified">
        Área administração do site.
        <a class="btn btn-danger" title="Clique aqui para voltar pra pagina inicial." href="{{ asset('/') }}">Pagina inicial</a>

    </div>
</nav>
-->




        <nav class="navbar navbar-expand navbar-dark bg-dark static-top ">
            <div class="container">
                <div class="navbar-header">
                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/adm') }}">
                        @yield('nomepagina')
                    </a>
                </div>
                <div class="nav-divider" style="width: 40%;"> &nbsp;</div>
                <div class="collapse navbar-collapse   float-right" >
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right" >
                        <!-- Authentication Links  -->
                        <li class="nav-item esp3"><a class="btn btn-sm btn-danger" href="{{ url('/') }}">Página Inicial</a></li>
                            <li class="nav-item esp3"><a class="btn btn-sm btn-danger" href="{{ route('login') }}">Login</a></li>
                            <li class="nav-item esp3"><a class="btn btn-sm btn-danger"href="{{ route('register') }}">Register</a></li>


                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')

<footer class="sticky-footer" style="width: 100%;margin-top: 100px;">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright © Filmes sem ADS 2018</span>
        </div>
    </div>
</footer>

    <!-- Scripts -->
<!-- Bootstrap core JavaScript-->
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- Core plugin JavaScript-->
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

<!-- Page level plugin JavaScript-->
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.js') }}"></script>

<!-- Custom scripts for all pages-->
<script src="{{ asset('js/sb-admin.min.js') }}"></script>

<!-- Demo scripts for this page-->
<script src="{{ asset('js/demo/datatables-demo.js') }}"></script>
<script src="{{ asset('js/demo/chart-area-demo.js') }}"></script>
<script src="https://unpkg.com/tippy.js@3/dist/tippy.all.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            placement: 'top',
            trigger: 'hover'
        });
    });
</script>

</body>
</html>
