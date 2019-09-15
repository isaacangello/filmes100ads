@extends('layouts.app')

@section('nomepagina')
    Filmes sem ADS - Resetar Senha
@endsection

@section('content')
<div class="container">
    <div class="card card-login mx-auto mt-5">
        <div class="card-header">Resetar Senha</div>
        <div class="card-body">
            <div class="text-center mb-4">
                <h4>Perdeu a senha?</h4>
                <p>Insira seu email para receber instruções para recuperar sua senha.</p>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                {{ csrf_field() }}
                <div class="form-group">
                    <div class="form-label-group">
                        <input type="email" id="inputEmail" name="email" value="{{ old('email') }}" class="form-control" placeholder="Enter email address" required="required" autofocus="autofocus">

                        @if ($errors->has('email'))
                            <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                        @endif

                        <label for="inputEmail">Enter email address</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Send Password Reset Link
                </button>
            </form>
            <div class="text-center">
                <a class="d-block small mt-3" href="{{ url('register') }}">Registrar Conta</a>
                <a class="d-block small" href="{{ url('login') }}">Fazer Login</a>
            </div>
        </div>
    </div>
</div>
<!--

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Reset Password</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Send Password Reset Link
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
-->
@endsection
