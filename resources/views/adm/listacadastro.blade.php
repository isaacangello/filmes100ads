@extends('adm.layout')

@section('content')
<div class="container container-fluid">
    @if(isset($sucesso) && $sucesso != " ")
    <div class="row">

        <div class="col-12">
            <div class="container container-fluid bg-success text-center text-dark">
               {{ $sucesso ?? ' ' }}
            </div>
        </div>
    </div>
    @endif
    <div class="row">

        <div class="col-12">
            <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped table-responsive-sm">
                <thead>
                <tr>
                    <th class="bg-info text-white" scope="col" style="text-align: center">#</th>
                    <th class="bg-info text-white" scope="col">Nome do Filme</th>
                    <th class="bg-info text-white" scope="col">Categoria</th>
                    <th class="bg-info text-white" scope="col">Status</th>
                    <th class="bg-info text-white" scope="col" style="text-align: center">Ações</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($posts_user as $post)
                    <tr>
                        <td scope="row" class="esp4em">{{ $post->id }}</td>
                        <td class="esp4em">{{ $post->name }}</td>
                        <td class="esp4em">{{ $post->categorias }}</td>
                        <td class="esp4em">{{ $post->status }}</td>
                        <td>
                            <form method="POST" action="#" enctype="multipart/form-data">
                                <input type="hidden" name="_method" value="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="editar" value="{{ $post->id }}">
                                <input type="hidden" name="pag" value="{{ $pag }}">
                                <input type="hidden" name="total_regs" value="{{ $total_regs }}">
                                <input type="hidden" name="exib_regs" value="{{ $exib_regs }}">
                                <button type="submit" class="btn btn-sm btn-outline-info float-left display-2" style="margin-right: 2px;">Editar</button>
                            </form>
                            <form method="POST" action="{{ route('meuscadastros.modstatus') }}" enctype="multipart/form-data">
                                <input type="hidden" name="_method" value="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="excluir" value="{{ $post->id }}">
                                <input type="hidden" name="pag" value="{{ $pag }}">
                                <input type="hidden" name="total_regs" value="{{ $total_regs }}">
                                <input type="hidden" name="exib_regs" value="{{ $exib_regs }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger float-left display-2">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>


    </div>
    <div class="row">
        <div class="col-12">
@if($pag )
            <nav aria-label="...">
                <ul class="pagination justify-content-center">
                    @if($pag == 1 )
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Anterior</a>
                        </li>
                        @else
                        <li class="page-item">
                            <a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag - 1)]) }}" tabindex="-1">Anterior</a>
                        </li>

                    @endif
                        @if (($pag-5) >= 1 )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag - 5 )]) }}">{{ ($pag - 5) }}</a></li>
                        @endif
                        @if (($pag-4) >= 1 )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag - 4 )]) }}">{{ ($pag - 4) }}</a></li>
                        @endif
                        @if (($pag-3) >= 1 )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag - 3 )]) }}">{{ ($pag - 3) }}</a></li>
                        @endif
                        @if (($pag-2) >= 1 )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag - 2)]) }}">{{ ($pag - 2) }}</a></li>
                        @endif
                        @if (($pag-1) >= 1 )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag - 1)]) }}">{{ ($pag - 1) }}</a></li>
                        @endif

                    <li class="page-item active">
                        <a class="page-link" href="#">{{ $pag }} <span class="sr-only">(atual)</span></a>
                    </li>
                    @if (($pag+1) < (ceil($total_regs/$exib_regs)) )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag + 1)]) }}">{{ ($pag + 1) }}</a></li>
                    @endif
                    @if (($pag+2) < (ceil($total_regs/$exib_regs)) )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag + 2)]) }}">{{ ($pag + 2) }}</a></li>
                    @endif
                    @if (($pag+3) < (ceil($total_regs/$exib_regs)) )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag + 3)]) }}">{{ ($pag + 3) }}</a></li>
                    @endif
                    @if (($pag+4) < (ceil($total_regs/$exib_regs)) )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag + 4)]) }}">{{ ($pag + 4) }}</a></li>
                    @endif
                    @if (($pag+5) < (ceil($total_regs/$exib_regs)) )
                            <li class="page-item"><a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag + 5)]) }}">{{ ($pag + 5) }}</a></li>
                    @endif

                 @if($pag >= ((ceil($total_regs/$exib_regs)) - 3) )
                    <li class="page-item disabled">
                        <a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag + 1)]) }}">Próximo</a>
                    </li>
                        @else
                    <li class="page-item ">
                        <a class="page-link" href="{{ route('meuscadastros.index', ['pag' => ($pag + 1)]) }}">Próximo</a>
                    </li>
                     @endif
                </ul>
            </nav>
@endif

        </div>
    </div>

</div>

@endsection
