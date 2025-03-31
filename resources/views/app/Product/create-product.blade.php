@extends('app.layout')
@section('title') Criação de Produtos & Negócios @endsection
@section('conteudo')

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"/>

    <div class="pagetitle">
        <h1>Criação de Produtos & Negócios</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('app') }}">Escritório</a></li>
                <li class="breadcrumb-item active">Criação de Produtos & Negócios</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <ul class="nav nav-tabs mt-3" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Detalhes</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">Contrato</button>
                            </li>
                        </ul>

                        <form action="{{ route('created-product') }}" method="POST" class="tab-content pt-2" id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                <div class="row">
                                    @csrf
                                    <div class="col-12 col-sm-12 col-md-7 col-lg-7 row">
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="form-floating mb-2">
                                                <input type="text" name="name" class="form-control" id="floatingName" placeholder="Título:" required>
                                                <label for="floatingName">Título:</label>
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-12 col-md-12 col-lg-127">
                                            <div class="form-floating mb-2">
                                                <textarea name="description" class="form-control" placeholder="Descrição" id="description" style="height: 150px;"></textarea>
                                                <label for="description">Descrição:</label>
                                            </div>
                                            <div class="form-floating mb-2">
                                                <textarea name="subject_terms" class="form-control" placeholder="Termos" id="subject_terms" style="height: 150px;"></textarea>
                                                <label for="subject_terms">Termos:</label>
                                            </div>
                                        </div>

                                        
                                    </div>
                                    <div class="col-12 col-md-5 col-lg-5">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_photo" id="request_photo">
                                            <label class="form-check-label" for="request_photo">Solicitar Foto (Rosto)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_document_photo" id="request_document_photo">
                                            <label class="form-check-label" for="request_document_photo">Solicitar Documento (RG, CNH etc)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_address" id="request_address">
                                            <label class="form-check-label" for="request_address">Solicitar Endereço</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_terms" id="request_terms">
                                            <label class="form-check-label" for="request_terms">Solicitar Aceite (Termos)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="request_contract" id="request_contract">
                                            <label class="form-check-label" for="request_contract">Solicitar Aceite (Contrato)</label>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-12 col-md-6 col-lg-6">
                                                <div class="form-floating mb-2">
                                                    <select name="access_level" class="form-select" id="access_level">
                                                        <option selected value="">Nível com acesso:</option>
                                                        <option value="6">VENDEDOR INTERNO</option>
                                                        <option value="1">INICIANTE</option>
                                                        <option value="2">CONSULTOR</option>
                                                        <option value="3">CONSULTOR LÍDER</option>
                                                        <option value="4">REGIONAL</option>
                                                        <option value="5">GERENTE REGIONAL</option>
                                                        <option value="7">DIRETOR</option>
                                                        <option value="8">DIRETOR REGIONAL</option>
                                                        <option value="9">PRESIDENTE VIP</option>
                                                        <option value="">TODOS</option>
                                                    </select>
                                                    <label for="access_level">Opções</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-6">
                                                <div class="form-floating mb-2">
                                                    <select name="status" class="form-select" id="status">
                                                        <option value="">Situação:</option>
                                                        <option value="1">Ativo</option>
                                                        <option value="2">Inativo</option>
                                                    </select>
                                                    <label for="status">Opções</label>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6 col-lg-6">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_cost" class="form-control" id="value_cost" placeholder="Custo:" oninput="mascaraReal(this)">
                                                    <label for="value_cost">Custo:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-6">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_rate" class="form-control" id="value_rate" placeholder="Taxas:" oninput="mascaraReal(this)">
                                                    <label for="value_rate">Taxas:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-6">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_min" class="form-control" id="value_min" placeholder="Mín de venda:" oninput="mascaraReal(this)">
                                                    <label for="value_min">Mín de venda:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-6">
                                                <div class="form-floating mb-2">
                                                    <input type="text" name="value_max" class="form-control" id="value_max" placeholder="Máx de venda:" oninput="mascaraReal(this)">
                                                    <label for="value_max">Máx de venda:</label>
                                                </div>
                                            </div>
                                            <div class="col-12 d-grid gap-2">
                                                <div class="btn-group">
                                                    <a href="{{ route('list-products') }}" class="btn btn-danger">Cancelar</a>
                                                    <button type="button" class="btn btn-outline-primary" onclick="openTab('#profile-tab')">Avançar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="col-12 col-md-12 col-lg-12 mb-2">
                                    <div id="editor"></div>
                                    <textarea id="subject_contract" name="subject_contract" style="display:none;"></textarea>
                                </div>
                                <div class="col-12 offset-md-7 col-md-5 offset-lg-7 col-lg-5 d-grid gap-2">
                                    <button type="submit" class="btn btn-primary" type="button">Cadastrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        function openTab(tab) {
            $(tab).click();
        }

        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': '1' }, { 'header': '2' }, { 'font': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['link', 'blockquote', 'code-block'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }]
                ]
            }
        });

        $('form').submit(function() {
            var content = quill.root.innerHTML;
            $('#subject_contract').val(content);
        });
    </script>
@endsection