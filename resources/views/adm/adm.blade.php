@extends('adm.layout')

@section('content')

    <!-- Botões adm-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-info o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-user-circle"></i>
                        </div>
                        <div class="mr-5">Configurações do Usuário</div>
                    </div>
                    <a class="card-footer text-white clearfix small z-1" href="#">
                        <span class="float-left">Ver Detalhes</span>
                        <span class="float-right">
                        <i class="fas fa-angle-right"></i>
                      </span>
                    </a>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <a class="text-white clearfix  z-1" href="{{ url('adm/cadastro') }}">
                        <div class="card text-white bg-info o-hidden h-100">
                            <div class="card-body">
                                <div class="card-body-icon">
                                    <i class="fas fa-fw fa-save"></i>
                                </div>
                                <div class="mr-5">Cadastrar Links/Filme(s)</div>
                            </div>
                            <span class="card-footer text-white clearfix small z-1">
                                <span class="float-left">Ver Detalhes</span>
                                <span class="float-right">
                                <i class="fas fa-angle-right"></i>
                              </span>
                            </span>
                        </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3">
                <a class="text-white clearfix  z-1" href="">
                    <div class="card text-white bg-info o-hidden h-100">
                        <div class="card-body">
                            <div class="card-body-icon">
                                <i class="fas fa-fw fa-list-alt"></i>
                            </div>
                            <div class="mr-5">Meus Filmes Cadastrados</div>
                        </div>
                        <span class="card-footer text-white clearfix small z-1" >
                            <span class="float-left">Ver Detalhes</span>
                            <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                          </span>
                        </span>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 mb-3">
                <a class="text-white clearfix  z-1" href="{{ url('adm/rendimentos') }}">
                    <div class="card text-white bg-success o-hidden h-100">
                        <div class="card-body">
                            <div class="card-body-icon">
                                <i class="fas fa-fw fa-comment-dollar"></i>
                            </div>
                            <div class="mr-5">Rendimentos</div>
                        </div>
                        <a class="card-footer text-white clearfix small z-1" href="#">
                            <span class="float-left">Ver Detalhes</span>
                            <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                          </span>
                        </a>
                    </div>
                </a>
            </div>


            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-info bg-light o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-comment-dots"></i>
                        </div>
                        <div class="mr-5">Ajuda</div>
                    </div>
                    <a class="card-footer text-info clearfix small z-1" href="#">
                        <span class="float-left">Ver Detalhes</span>
                        <span class="float-right">
                        <i class="fas fa-angle-right"></i>
                      </span>
                    </a>
                </div>
            </div>
        </div>

    </div>


@endsection