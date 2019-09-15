<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cortando imagens</title>
    <!-- Bootstrap core CSS-->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">


    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Page level plugin CSS-->
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/sb-admin.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/append.css') }}" rel="stylesheet" type="text/css">



    <!-- css para crop-->
    <link href="{{ asset('vendor/jcrop/jquery.Jcrop.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('vendor/jcrop/jcrop_demos.css') }}" rel="stylesheet" type="text/css">

    <!-- script para crop-->

    <script src="{{ asset('vendor/jcrop/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jcrop/jquery.Jcrop.min.js') }}"></script>
    <!--<script src="{{ asset('vendor/jcrop/jcrop_demos.js') }}"></script>-->
    <script language="Javascript">

        $(function(){

            $('#cropbox').Jcrop({
                aspectRatio: 11 / 16 ,
                bgColor: '#17a2b8',
                bgOpacity: .6 ,
                minSize: [150, 210 ] ,
                //maxSize: [150, 210 ] ,
                onSelect: updateCoords
            });

        });

        function updateCoords(c)
        {
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
        };

        function checkCoords()
        {
            if (parseInt($('#w').val())) return true;
            alert('Selecione a região para recortar a imagem.');
            return false;
        };

    </script>

</head>
<body id="page-top" style="background-color: white;">
<nav class="navbar navbar-expand navbar-dark bg-dark static-top">
&nbsp;
</nav>
    <div class="container container-fluid">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ url('adm') }}">Adm</a>
            </li>
            <li class="breadcrumb-item"><a href="{{ route('cadastro.index') }}" >Voltar</a></li>

        </ol>
        <div class="moldura">
            <div class="moldura_header"></div>
            <div class="moldura_body">

                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-3"></div>
                    <div class="col-auto">
                        <!-- Imagem que vamos inserir -->
                        <img src="{{ asset('storage/temp/tempimage'.Auth::user()->id.'.jpg') }}" id="cropbox" style="margin-bottom: 20px;" />

                    </div>
                    <div class="col-auto"></div>

                </div>
                <div class="row">

                    <div class="col-12">
                        <!-- Formulário para realização do crop-->
                        <form action="{{ route('cadastro.salvando') }}" method="post" enctype="multipart/form-data" onsubmit="return checkCoords();">
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="crop" value="1">
                            <input type="hidden" id="x" name="x" />
                            <input type="hidden" id="y" name="y" />
                            <input type="hidden" id="w" name="w" />
                            <input type="hidden" id="h" name="h" />
                            <input class="btn btn-info container-fluid" type="submit" value="Crop Image" />
                        </form>

                    </div>
                </div>

            </div>

        </div>


    </div>

</body>
</html>