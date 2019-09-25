@extends('adm.layout')
@section('navegacao')
    &nbsp;/ &nbsp;<a href="">Cadastro</a>
@endsection
@section('content')

<div class="row container-fluid">
    <div class="col-md-1"></div>
    <div class="col-md-10">
                @if(isset($sucess) )
                    <div class="alert alert-success">
                        {{ $sucess }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

        <div class="moldura">
            <div class="moldura_header text-white"> <h5>Cadastro de Links / Filmes</h5></div>
            <div class="moldura_body">

                    <form method="POST" action="{{ route('cadastro.store') }}" enctype="multipart/form-data">
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="tocrop" value="1">

                        <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">
                                        <input type="text" id="name" name="name" class="form-control" placeholder="Titulo do Filme" required="required" autofocus="autofocus">
                                        <label for="name">Titulo do Filme</label>
                                            @if ($errors->has('name'))
                                                <span class="help-block">
                                                        <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                            @endif

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group {{ $errors->has('realname') ? ' has-error' : '' }}{{ $errors->has('categoria') ? ' has-error' : '' }}">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-label-group">
                                        <input type="text" id="realname"  name="realname" class="form-control" placeholder="Titulo origunal" required="required" autofocus="autofocus">
                                        <label for="realname">Titulo Original</label>
                                        @if ($errors->has('realname'))
                                            <span class="help-block">
                                                        <strong>{{ $errors->first('realname') }}</strong>
                                                </span>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-label-group">
                                        <select name="categoria" class="browser-default text-md-right custom-select custom-select-lg mb-3" style="font-size: 1rem;">
                                            <option selected>Categorias</option>
                                        @if($categorias)
                                            @foreach($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->name }}</option>
                                            @endforeach
                                        @endif
                                        </select>
                                        @if ($errors->has('categoria'))
                                            <span class="help-block">
                                                        <strong>{{ $errors->first('categoria') }}</strong>
                                                </span>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('diretor') ? ' has-error' : '' }}{{ $errors->has('duracao') ? ' has-error' : '' }}">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-label-group">
                                        <input type="text" id="diretor" name="diretor" class="form-control" placeholder="Diretor" required="required" autofocus="autofocus">
                                        <label for="diretor">Diretor</label>
                                        @if ($errors->has('diretor'))
                                            <span class="help-block">
                                                    <strong>{{ $errors->first('diretor') }}</strong>
                                            </span>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-label-group" style="padding-top: 6%;padding-left: 10%;">
                                        <input name="duracao" type="hidden" class="slider-input slider range-slider" value="120" />
                                        <label style="position: relative; top:-4em;left:8em;" for="duracao">Duraçao do Filme em Minutos.</label>
                                        @if ($errors->has('duracao'))
                                            <span class="help-block">
                                                    <strong>{{ $errors->first('duracao') }}</strong>
                                            </span>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-row">


                                <div class="col-md-6">
                                    <div class="form-label-group">
                                        <select name="pais" class="browser-default text-md-right custom-select custom-select-lg mb-3" style="font-size: 1rem;">
                                            <option selected>Pais de Origem</option>
                                            @foreach($paises as $pais)
                                                <option value="{{ $pais->nome }}">{{ $pais->nome }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('categoria'))
                                            <span class="help-block">
                                                        <strong>{{ $errors->first('categoria') }}</strong>
                                                </span>
                                        @endif

                                    </div>
                                </div>


                                <!--
                                <div class="col-md-6">
                                    <div class="form-label-group">
                                        <input type="text" id="pais" name="pais" class="form-control" placeholder="Pais de Origem" required="required" autofocus="autofocus">
                                        <label for="pais">Pais de Origem</label>
                                    </div>
                                </div>
                                -->



                                <div class="col-md-6">
                                    <div class="form-label-group">
                                        <select name="ano" class="browser-default text-md-right custom-select custom-select-lg mb-3 " style="font-size: 1rem;">
                                            <option selected>Ano de Lançamento</option>
                                            @for ($i = 2022; $i >= 1910; $i--)

                                                <option value="{{ $i }}">{{ $i }}</option>

                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="form-group{{ $errors->has('capa') ? ' has-error' : '' }}">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="input-group mb-12">
                                        <div class="input-group-prepend ">
                                            <span class="input-group-text ">Enviar</span>
                                        </div>
                                        <div class="custom-file">
                                            <input name="capa" placeholder="Escolher" type="file" class="custom-file-input" id="inputGroupFileCapa">
                                            <label class="custom-file-label" for="inputGroupFileCapa">Escolha uma Foto de Capa</label>
                                            @if ($errors->has('capa'))
                                                <span class="help-block">
                                                        <strong>{{ $errors->first('capa') }}</strong>
                                                </span>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="sinopse">Escreva uma sinopse.</label>
                                            <textarea name="sinopse" id="sinopse" placeholder="Escreva uma sinopse" class="form-control"  rows="6"></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="labelurl1">Insira uma identificação para URL 1</label>
                                            <input name="labelurl1" id="labelurl1" type="text" placeholder="Insira um nome do servidor. para URL 1" class="form-control" >
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="url1">Insira a URL 1</label>
                                            <textarea name="url1" id="url1" placeholder="Insira a Url" class="form-control"  rows="4"></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="labelurl2">Insira uma identificação para URL 2</label>
                                            <input name="labelurl2" id="labelurl2" type="text" placeholder="Insira um nome do servidor. para URL 2" class="form-control" >

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="url2">Insira a URL 2</label>
                                            <textarea name="url2" id="url2" placeholder="Insira a Url" class="form-control"  rows="4"></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="labelurl3">Insira uma identificação para URL 3</label>
                                            <input name="labelurl3" id="labelurl3" type="text" placeholder="Insira um nome do servidor. para URL 3" class="form-control" >

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="url1">Insira a URL 3</label>
                                            <textarea name="url3" id="url3" placeholder="Insira a Url" class="form-control"  rows="4"></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="labelurl4">Insira uma identificação para URL 4</label>
                                            <input name="labelurl4" id="labelurl4" type="text" placeholder="Insira um nome do servidor. para URL 4" class="form-control" >
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="url1">Insira a URL 4</label>
                                            <textarea name="url4" id="url4" placeholder="Insira a Url" class="form-control"  rows="4"></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="labelurl5">Insira uma identificação para URL 5</label>
                                            <input name="labelurl5" id="labelurl5" type="text" placeholder="Insira um nome do servidor. para URL 5" class="form-control" >
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="url1">Insira a URL 5</label>
                                            <textarea name="url5" id="url5" placeholder="Insira a Url" class="form-control"  rows="4"></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="labelurl6">Insira uma identificação para URL 6</label>
                                            <input name="labelurl6" id="labelurl6" type="text" placeholder="Insira um nome do servidor. para URL 6" class="form-control" >

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-label-group">

                                        <div class="form-group">
                                            <label for="url1">Insira a URL 6</label>
                                            <textarea name="url6" id="url6" placeholder="Insira a Url" class="form-control"  rows="4"></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                    <button class="btn btn-info btn-lg container-fluid" type="submit" >
                        Salvar
                    </button>
                    </form>


            </div>
        </div>
    </div>
    <div class="col-md-1"></div>
</div>
<div style="width: 100%; height: 200px;"></div>
@endsection
