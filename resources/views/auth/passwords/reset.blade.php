@extends('layouts.app')

@section('nomepagina')
    Filmes sem ADS - Criar nova Senha
@endsection

@section('content')
    <div class="container">
        <div class="card card-register mx-auto mt-5">
            <div class="card-header">Resetar Senha (Proximo  passo é criar um nova senha.)</div>
            <div class="card-body">
                <form class="form-horizontal" method="POST" action="{{ route('password.request') }}">
                    {{ csrf_field() }}

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <div class="form-label-group">
                            <input name="email" value="{{ $email or old('email') }}" type="email" id="inputEmail" class="form-control" placeholder="Email address" required="required">

                            @if ($errors->has('email'))
                                <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif

<!-- -->
                            <label for="inputEmail">Email address</label>
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input name="password" type="password" id="inputPassword" class="form-control" placeholder="Password" required="required">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif

                                    <label for="inputPassword">Password</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input name="password_confirmation"  type="password" id="confirmPassword" class="form-control" placeholder="Confirm password" required="required">

                                        @if ($errors->has('password_confirmation'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            </span>
                                        @endif


                                    <label for="confirmPassword">Confirm password</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%">
                        Criar nova senha
                    </button>
                </form>
                <div class="text-center">
                    <a class="d-block small mt-3" href="{{url('/register')}}">Register an Account</a>
                    <a class="d-block small" href="{{ url('/login') }}">Login Page</a>
                </div>
            </div>
        </div>
    </div>
<!--
<div class="container" style="clear: both;">

    <div class="card card-login mx-auto mt-5">
        <div class="card-header">Resetar Senha</div>
        <div class="card-body">
            <div class="text-center mb-4">
                <h5>Gerar Nova senha</h5>
                <p>Proximo  passo é criar um nova senha.</p>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('password.request') }}">
                {{ csrf_field() }}

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <div class="form-label-group">
                        <label for="email">E-Mail Address</label>

                            <input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                    </div>
                </div>

                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">

                    <div class="form-label-group">
                        <label for="password" class="control-label">Password</label>


                            <input id="password" type="password" class="form-control" name="password" required>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                    </div>

                </div>

                <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                    <div class="form-label-group">
                        <label for="password-confirm" class="control-label">Confirm Password</label>

                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                            @if ($errors->has('password_confirmation'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif
                    </div>

                </div>

                <div class="form-group">

                        <button type="submit" class="btn btn-primary" style="width: 100%">
                            Criar senha
                        </button>

                </div>


            </form>
            <div class="text-center">
                <a class="d-block small mt-3" href="{{url('/register')}}">Register an Account</a>
                <a class="d-block small" href="{{ url('/login') }}">Login Page</a>
            </div>
        </div>
    </div>
</div>

-->


@endsection
