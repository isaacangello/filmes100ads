@extends('adm.layout')
@section('navegacao')
    &nbsp;/ &nbsp;<a href="">Cadastro</a>
@endsection


@section('content')
<div class="container-fluid">
    <form>
        <div class="row">
            <div class="col-2"></div>
            <div class="col-xl-8 col-sm-6">
                <div class="panel ">
                    <div class="panel-heading panel-info">Cadastrar de Link/Filmes</div>
                    <div class="panel-body ">



                <div class="row">

                    <div class="col-xl-12 col-md-12 ">
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-6">

                                    <div class="form-label-group">
                                        <input type="text" id="filmename" name="nomefime" class="form-control" placeholder="Nome do filme" required="required" autofocus="autofocus">
                                        <label for="filmename">Nome do fimes</label>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-label-group">

                                            <select class="btn-info select-append " id="cadcategorias">
                                                <option class="select-append-item">Categorias</option>
                                                <option class="select-append-item">2</option>
                                                <option class="select-append-item">3</option>
                                                <option class="select-append-item">4</option>
                                                <option class="select-append-item">5</option>
                                            </select>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>




                    <div class="col-xl-12 col-md-12 ">
                        <div c]ass="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">
                                        <input type="text" id="lastName" class="form-control" placeholder="Last name" required="required">
                                        <label for="lastName">Last name</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto"></div>
                </div>


            </div>

            </div>
        </div><!--col-->

            </div> <!--row-->
            </div><!--container-fuid-->





    </form>
</div>

@endsection