@extends('posts.layout')
@section('alerta')
@if ($message = Session::get('success'))
<div class='row'>
  <div class='col-md-12'>
    <div class="alert alert-success">
      <p>{{ $message }}</p>
    </div>
  </div>
</div>
@endif
@endsection
@section('navegacao')
    @if($categoria_nome)

        <li class="breadcrumb-item active">Categoria</li>
    <li class="breadcrumb-item active">{{ $categoria_nome->name }}</li>
    @endif
@endsection
@section('categorias')
  @foreach($categorias as $dado)
    <a class="dropdown-item" href="{{ route('index',[ 'categorias' => $dado->id ]) }}" >{{ $dado->name }}</a>

  @endforeach
@endsection


@section('content')
 @if ($visualizar != false)
        <div class="row container-fluid">
         <div class="col-xl-2 col-sm-2 mb-2">&nbsp;</div>
         <div class="col-xl-8 col-sm-8 mb-8 border-primary">
             <table class="table table-responsive-xl">
                 <tbody>
                 <tr>
                     <td style="width: 30%;margin-right: 10px;padding-right: 0px;">
                         <img  class="img-rounded img-responsive img-thumbnail" style="max-width: 150px !important; " src="{{ url('/capas/exemplocapa.jpg') }}" alt="logo" />
                     </td>
                     <td>
                         <table class="table table-sm table-responsive-sm">
                             <tbody>
                                <tr>
                                    <td class="bg-danger" style="color: #FFFFFF;">Filme:&nbsp;{{ $visualizar['name'] }}</td>
                                </tr>
                                <tr><td>Categoria:&nbsp;{{ $visualizar['categoria'] }}</td></tr>
                                <tr><td>duraç&atilde;o:&nbsp;</td></tr>
                                <tr><td>Ano:&nbsp;</td></tr>
                             </tbody>
                         </table>
                     </td>
                 </tr>
                 <tr>
                     <td colspan="2">
                         <p class="mb-0">
                             Sinopse:&nbsp;{{ $visualizar['sinopse'] }}
                         </p>
                     </td>
                 </tr>
                 </tbody>
             </table>
         </div>
         <div class="col-xl-2 col-sm-2 mb-2">&nbsp;</div>
        </div>
        <div class="row container-fluid">
            <div class="col col-xl-12 col-sm-12 mb-2">
                <table class="table table-responsive-xl">
                    <tbody>
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col-4">
                                    <div class="list-group" id="list-tab" role="tablist">
                                        @foreach( $urls_visualizar as $label)
                                            @if ($loop->first)
                                                <a class="list-group-item list-group-item-action active" id="{{ $label->label }}{{ $loop->iteration }}-list" data-toggle="list" href="#{{ $label->label }}{{ $loop->iteration }}" role="tab" aria-controls="{{ $label->label }}">{{ $label->label }}</a>
                                            @else
                                                <a class="list-group-item list-group-item-action" id="{{ $label->label }}{{ $loop->iteration }}-list" data-toggle="list" href="#{{ $label->label }}{{ $loop->iteration }}" role="tab" aria-controls="{{ $label->label }}">{{ $label->label }}</a>
                                            @endif
                                        @endforeach
                                        <!--

                                        <a class="list-group-item list-group-item-action" id="list-servidor3-list" data-toggle="list" href="#list-servidor3" role="tab" aria-controls="messages">Servidor 3</a>
                                        <a class="list-group-item list-group-item-action" id="list-servidor4-list" data-toggle="list" href="#list-servidor4" role="tab" aria-controls="settings">Servidor 4</a>
                                            -->
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="tab-content" id="nav-tabContent">
                                        @foreach( $urls_visualizar as $url)
                                            @if ($loop->first)
                                                @php
                                                 $string =  $url->url;
                                                 $string = preg_replace('/width=\"[0-9][0-9][0-9]\"/','style="width:100%;min-height: 300px;"',$string);
                                                $string = preg_replace('/height=\"[0-9][0-9][0-9]\"/',' ',$string);
                                                @endphp
                                                <div class="tab-pane fade show active" id="{{ $label->label }}{{ $loop->iteration }}" role="tabpanel" aria-labelledby="{{ $label->label }}-list">{!! $string  !!}   </div>
                                            @else
                                                @php
                                                    $string =  $url->url;
                                                    $string = preg_replace('/width=\"[0-9][0-9][0-9]\"/','style="width:100%;min-height: 300px;"',$string);
                                                   $string = preg_replace('/height=\"[0-9][0-9][0-9]\"/',' ',$string);
                                                @endphp
                                                <div class="tab-pane fade show " id="{{ $label->label }}{{ $loop->iteration }}" role="tabpanel" aria-labelledby="{{ $label->label }}-list">{!! $string  !!}   </div>

                                            @endif
                                        @endforeach
                                        <!--
                                        <div class="tab-pane fade" id="list-servidor2" role="tabpanel" aria-labelledby="list-profile-list">servidor 2</div>
                                        <div class="tab-pane fade" id="list-servidor3" role="tabpanel" aria-labelledby="list-messages-list">servidor 3</div>
                                        <div class="tab-pane fade" id="list-servidor4" role="tabpanel" aria-labelledby="list-settings-list">servidor 4</div>
                                        -->
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
 @else
    @if ($posts)

        @foreach ($posts  as $post)
        <a  href="{{ route('index', ['filmeid' => $post->id ]) }}" title="{{ $post->name }}" data-tippy="<div class='toltip'><h1>{{ $post->name }}</h1></div>">
        <div class="col-xl-2 col-sm-2 mb-2" >
          <div class="card text-white bg-danger o-hidden h-100">
            <div class="card-body">
              <div class="card-body-icon">
              <!--  <i class="fas fa-fw fa-life-ring"></i>-->
              </div>
              <div class="mr-0">
                  <img src="{{ url('/capas/exemplocapa.jpg') }}" alt="logo" />
              </div>
            </div>
            <a class="card-footer text-white clearfix small z-1" href="{{ route('index', ['filmeid' => $post->id ]) }}">
              <span class="float-left" data-toggle="popover" data-placement="top" title="{{ $post->name }}" >
                View Details
              </span>
              <span class="float-right">
                <i class="fas fa-angle-right"></i>
              </span>
            </a>
          </div>
        </div>
        </a>
         @endforeach

    @else<!--if post -->

          <div class="col-xl-12 col-sm-12 mb-12">
            <div class="alert alert-info">
              <strong>Informação!</strong> nenhum resultado encontrado.
            </div>
          </div>

    @endif<!--if post -->
    <!--inicio da paginaçao -->
        <div class="col-xl-12 col-sm-12 mb-12" style="margin-bottom: 20px;">
            <ul class="pagination">

                @if ($cate_var)<!--if cate_var -->
                    @if ($pagina_atual == 1)

                        <li class="page-item disabled">
                          <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                          </a>
                        </li>

                    @else

                          <li class="page-item">
                              <a class="page-link" href="{{ route('index',[ 'pag' => ($pagina_atual -1) , 'categorias' => $cate_var ]) }}" aria-label="Previous">
                                  <span aria-hidden="true">&laquo;</span>
                                  <span class="sr-only">Previous</span>
                              </a>
                          </li>

                    @endif

                    @if($pagina_atual == $total_pages)

                          <li class="page-item disabled">
                              <a class="page-link" href="#" aria-label="Next">
                                  <span aria-hidden="true">&raquo;</span>
                                  <span class="sr-only">Next</span>
                              </a>
                          </li>


                    @else

                          <li class="page-item">
                              <a class="page-link" href="{{ route('index',[ 'pag' => ($pagina_atual +1) , 'categorias' => $cate_var ]) }}" aria-label="Next">
                                  <span aria-hidden="true">&raquo;</span>
                                  <span class="sr-only">Next</span>
                              </a>
                          </li>
                    @endif

                @else<!--if cate_var -->

                    @if ($pagina_atual == 1)

                      <li class="page-item disabled">
                          <a class="page-link" href="#" aria-label="Previous">
                              <span aria-hidden="true">&laquo;</span>
                              <span class="sr-only">Previous</span>
                          </a>
                      </li>

                    @else

                      <li class="page-item">
                          <a class="page-link" href="{{ route('index',[ 'pag' => ($pagina_atual -1)  ]) }}" aria-label="Previous">
                              <span aria-hidden="true">&laquo;</span>
                              <span class="sr-only">Previous</span>
                          </a>
                      </li>

                    @endif

                        @if($pagina_atual == $total_pages)

                          <li class="page-item disabled">
                              <a class="page-link" href="#" aria-label="Next">
                                  <span aria-hidden="true">&raquo;</span>
                                  <span class="sr-only">Next</span>
                              </a>
                          </li>


                        @else

                          <li class="page-item">
                              <a class="page-link" href="{{ route('index',[ 'pag' => ($pagina_atual +1)  ]) }}" aria-label="Next">
                                  <span aria-hidden="true">&raquo;</span>
                                  <span class="sr-only">Next</span>
                              </a>
                          </li>
                        @endif

                @endif<!--if cate_var -->
            </ul>

                @if ($cate_var)

                        @for($i = 1;$i <=$total_pages ;$i++)

                              @if ($i == $pagina_atual)
                                <div class="page-item active" style="width:4.7%; float: left;text-align: center;">
                                  <a class="page-link" href="{{ route('index',[ 'pag' => $i , 'categorias' => $cate_var ]) }}">{{ $i }}</a>
                                </div>
                              @else
                                <div class="page-item "style="width:4.7%;float: left;text-align: center;">
                                  <a class="page-link" href="{{ route('index',[ 'pag' => $i , 'categorias' => $cate_var]) }}">{{ $i }}</a>
                                </div>
                              @endif

                        @endfor

                @else

                        @for($i = 1;$i <=$total_pages ;$i++)

                              @if ($i == $pagina_atual)
                                  <div class="page-item active" style="width:4.7%; float: left;text-align: center;">
                                    <a class="page-link" href="{{ route('index',[ 'pag' => $i ]) }}">{{ $i }}</a>
                                  </div>
                              @else
                                  <div class="page-item "style="width:4.7%;float: left;text-align: center;">
                                    <a class="page-link" href="{{ route('index',[ 'pag' => $i ]) }}">{{ $i }}</a>
                                  </div>
                              @endif

                        @endfor

                @endif
        </div>
@endif
@endsection
