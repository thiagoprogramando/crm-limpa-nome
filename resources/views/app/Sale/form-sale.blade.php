@extends('layout')    
@section('content')
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-10">
                    <div class="wrap d-md-flex">
                        <div class="img" style="background-image: url({{ asset('login-form/images/wallpper_external_sale.png') }});"></div>
                        <div class="login-wrap p-5 p-md-5">
                            <div class="d-flex">
                                <div class="w-100 text-center">
                                    <h3>{{ $link->title ?? 'Bem-vindo(a)' }}</h3>
                                    <small>{{ $link->description ?? 'Preencha todos os dados' }}</small>
                                </div>
                            </div>

                            <form action="{{ route('created-external-sale', ['product' => $link->product_id, 'link' => $link->uuid]) }}" method="POST" class="signin-form">
                                @csrf
                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible" role="alert">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                <input type="hidden" name="payment_method" value="{{ $link->payment_method }}">
                                <input type="hidden" name="payment_installments" value="{{ $link->payment_installments }}">
                                <input type="hidden" name="payment_json_installments" value='@json($link->payment_json_installments)'>
                                <div class="form-group mb-3">
                                    <label class="label" for="name">Nome Completo:</label>
                                    <input type="text" name="name" class="form-control" placeholder="Ex São Bento" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="label" for="name">CPF/CNPJ:</label>
                                    <input type="text" name="cpfcnpj" class="form-control" placeholder="000.000.000-00" oninput="maskCpfCnpj(this)" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="label" for="birth_date">Data de Nascimento:</label>
                                    <input type="date" name="birth_date" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="label" for="phone">WhatsApp ou Telefone:</label>
                                    <input type="text" name="phone" class="form-control" placeholder="(55) 98899-7766" oninput="maskPhone(this)" required>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="form-control btn btn-primary rounded submit px-3">Enviar Dados</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
