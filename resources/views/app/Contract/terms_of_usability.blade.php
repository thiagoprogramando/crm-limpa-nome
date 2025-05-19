<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        <title>{{ $title }}</title>
        <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="{{ asset('login_template/css/style.css') }}">
        <style>

            body {
                font-family: 'Times New Roman', Times, serif;
            }

            .container {
                background-color: #fff !important;
            }

            .floating-button {
                position: fixed;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                background-color: #007bff;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease-in-out;
                z-index: 9999;
            }

            canvas {
                width: 100%;
                height: auto;
                display: block;
                background-color: white;
                touch-action: none;
            }
        </style>
    </head>
    <body>

        <div class="container mt-5 mb-5 p-5">
            @if (Auth::user()->terms_of_usability)
                {!! Auth::user()->terms_of_usability !!}
            @else
                <h1>Contrato de Adesão e Uso do Sistema – Vendedores Parceiros</h1>
                <p><strong>PARTES:</strong></p>
                <p>
                    De um lado, a empresa <strong>{{ env('COMPANY_NAME') }}</strong>, inscrita no CNPJ sob nº <strong>{{ env('COMPANY_CPFCNPJ') }}</strong>, com sede na <strong>{{ env('COMPANY_ADDRESS') }}</strong>, e-mail <strong>{{ env('COMPANY_EMAIL') }}</strong>, doravante denominada <strong>“CONTRATANTE” ou “CRM”</strong>;
                </p>
                <p>
                    E de outro, <strong>{{ Auth::user()->company_name }}</strong>, portador do CPF/CNPJ nº <strong>{{ Auth::user()->company_cpfcnpj }}</strong>, residente e domiciliado na <strong>{{ Auth::user()->company_address }}</strong>, email <strong>{{ Auth::user()->company_email }}</strong>, doravante denominado <strong>“VENDEDOR PARCEIRO” ou “USUÁRIO”</strong>.
                </p>

                <p>
                    As partes acima identificadas resolvem firmar o presente <strong>Contrato de Adesão e Uso de Sistema</strong>, que se regerá pelas cláusulas e condições seguintes:
                </p>

                <h2>1. Objeto</h2>
                <p>1.1. O presente contrato tem por objeto a <strong>adesão e uso do sistema CRM</strong> desenvolvido pela CONTRATANTE, voltado à gestão de vendas de serviços de negociação de dívidas (popularmente conhecido como “Limpa Nome”), por meio de plataforma própria.</p>
                <p>1.2. Será de total responsabilidade da CONTRATANTE <strong>atribuir ou adicionar novos produtos</strong>, assim como gerenciar o uso/seguro dos mesmos.</p>

                <h2>2. Natureza da Relação</h2>
                <p>2.1. O presente contrato é de <strong>adesão e prestação de serviço de sistema</strong>, não estabelecendo vínculo empregatício, representação comercial, sociedade ou franquia entre as partes.</p>

                <h2>3. Garantia dos Produtos</h2>
                <p>3.1. Os produtos e serviços ofertados através do sistema possuem <strong>garantia de 3 (três) meses</strong>, a contar da data da venda, podendo este prazo ser ampliado pela CONTRATANTE, a seu exclusivo critério.</p>

                <h2>4. Prazo Mínimo de Uso</h2>
                <p>4.1. O VENDEDOR PARCEIRO compromete-se a <strong>utilizar o sistema por um prazo mínimo de 6 (seis) meses</strong>, a contar da data de ativação da sua conta no CRM.</p>

                <h2>5. Regras de Comercialização</h2>
                <p>5.1. O não cumprimento das <strong>condições contratuais dos produtos ofertados</strong>, especialmente as cláusulas de elegibilidade, prazos e documentos exigidos, <strong>invalida a venda</strong> realizada pelo VENDEDOR PARCEIRO.</p>
                <p>5.2. O VENDEDOR PARCEIRO é integralmente responsável pelas <strong>informações prestadas aos clientes finais</strong>, devendo seguir fielmente as diretrizes e orientações fornecidas dentro do CRM.</p>

                <h2>6. Responsabilidade por Serviços e Estornos</h2>
                <p>6.1. A CONTRATANTE compromete-se a <strong>estornar os valores recebidos dos clientes finais</strong>, em caso de <strong>não entrega ou não execução dos serviços contratados</strong>, <strong>excetuando-se os valores pagos a título de assinatura</strong> ou uso do sistema pelo VENDEDOR PARCEIRO, que <strong>não são reembolsáveis</strong>.</p>

                <h2>7. Limitação de Responsabilidade</h2>
                <p>7.1. A CONTRATANTE atua <strong>exclusivamente como fornecedora de tecnologia de gestão e intermediação</strong>, não sendo responsável pela comercialização direta dos serviços ou pela atuação dos VENDEDOR PARCEIROS junto ao público.</p>

                <h2>8. Rescisão</h2>
                <p>8.1. O presente contrato poderá ser rescindido a qualquer tempo por qualquer das partes, mediante notificação por escrito, respeitado o <strong>prazo mínimo de 6 (seis) meses</strong> de uso.</p>
                <p>8.2. A CONTRATANTE reserva-se o direito de <strong>suspender ou encerrar</strong> o acesso do VENDEDOR PARCEIRO em caso de descumprimento de qualquer cláusula deste contrato.</p>

                <h2>9. Disposições Gerais</h2>
                <p>9.1. Este contrato é regido pelas leis brasileiras e qualquer controvérsia será dirimida no foro da comarca de <strong>Natal/RN</strong>, com exclusão de qualquer outro, por mais privilegiado que seja.</p>

                <p><strong>Declaro que li, compreendi e aceito integralmente os termos deste contrato.</strong></p>
                <p>__________________________________________</p>
                <p><strong>VENDEDOR PARCEIRO:</strong> {{ Auth::user()->company_name }}</p>
                <p><strong>Data:</strong> {{ now()->format('d/m/Y') }}</p>
            @endif
        </div>
        
        @if (empty(Auth::user()->terms_of_usability))
            <button id="floatingButton" class="floating-button btn btn-primary">
                <i class="ri-add-line"></i> Assinar Contrato
            </button>
        @else
            <button type="button" onclick="print()" class="floating-button btn btn-primary">
                <i class="ri-add-line"></i> Imprimir
            </button>
        @endif

        <div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="signatureModalLabel">Assinatura Digital</h5>
                    </div>
                    <div class="modal-body text-center">
                        <canvas id="signaturePad" width="400" height="200" style="border: 1px solid #000; touch-action: none;"></canvas>
                    </div>
                    <div class="modal-footer btn-group">
                        <button type="button" id="clearSignature" class="btn btn-danger text-light">Limpar</button>
                        <button type="button" id="saveSignature" class="btn btn-primary">Assinar</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('login_template/js/jquery.min.js') }}"></script>
        <script src="{{ asset('login_template/js/popper.js') }}"></script>
        <script src="{{ asset('login_template/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('login_template/js/main.js') }}"></script>
        <script src="{{ asset('assets/js/sweetalert.js')}}"></script>
        <script src="{{ asset('login_template/js/signature_pad.js') }}"></script>

        <script>
            @if(session('error'))
                Swal.fire({
                    title: 'Erro!',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    timer: 2000
                })
            @endif

            @if(session('info'))
                Swal.fire({
                    title: 'Atenção!',
                    text: '{{ session('info') }}',
                    icon: 'info',
                    timer: 2000
                })
            @endif
            
            @if(session('success'))
                Swal.fire({
                    title: 'Sucesso!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    timer: 2000
                })
            @endif


            document.addEventListener("DOMContentLoaded", function () {
                var canvas = document.getElementById('signaturePad');
                var ctx = canvas.getContext("2d");
                var isDrawing = false;
                var lastX = 0;
                var lastY = 0;

                function resizeCanvas() {
                    var modalBody = document.querySelector('.modal-body');
                    var width = modalBody.clientWidth - 40;
                    canvas.width = width > 400 ? 400 : width;
                    canvas.height = 200;
                    ctx.fillStyle = "white";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                }

                document.getElementById('floatingButton').addEventListener('click', function () {
                    var modal = new bootstrap.Modal(document.getElementById('signatureModal'));
                    modal.show();
                });

                document.getElementById('signatureModal').addEventListener('shown.bs.modal', resizeCanvas);

                function startDrawing(e) {
                    isDrawing = true;
                    [lastX, lastY] = [e.offsetX, e.offsetY];
                }

                function draw(e) {
                    if (!isDrawing) return;
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(e.offsetX, e.offsetY);
                    ctx.strokeStyle = "#000";
                    ctx.lineWidth = 2;
                    ctx.lineCap = "round";
                    ctx.stroke();
                    [lastX, lastY] = [e.offsetX, e.offsetY];
                }

                function stopDrawing() {
                    isDrawing = false;
                }

                canvas.addEventListener("mousedown", startDrawing);
                canvas.addEventListener("mousemove", draw);
                canvas.addEventListener("mouseup", stopDrawing);
                canvas.addEventListener("mouseout", stopDrawing);

                canvas.addEventListener("touchstart", function (e) {
                    var touch = e.touches[0];
                    var rect = canvas.getBoundingClientRect();
                    lastX = touch.clientX - rect.left;
                    lastY = touch.clientY - rect.top;
                    isDrawing = true;
                });

                canvas.addEventListener("touchmove", function (e) {
                    if (!isDrawing) return;
                    var touch = e.touches[0];
                    var rect = canvas.getBoundingClientRect();
                    var x = touch.clientX - rect.left;
                    var y = touch.clientY - rect.top;

                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(x, y);
                    ctx.strokeStyle = "#000";
                    ctx.lineWidth = 2;
                    ctx.lineCap = "round";
                    ctx.stroke();
                    [lastX, lastY] = [x, y];

                    e.preventDefault();
                });

                canvas.addEventListener("touchend", function () {
                    isDrawing = false;
                });

                document.getElementById('clearSignature').addEventListener('click', function () {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                });

                document.getElementById('saveSignature').addEventListener('click', function () {
                    if (canvas.toDataURL() === ctx.fillStyle) {
                        Swal.fire({
                            title: 'Atenção!',
                            text: 'É necessário Assinar o Contrato!',
                            icon: 'info',
                            timer: 2000
                        });
                        return;
                    }

                    var signatureData = canvas.toDataURL("image/png");
                    var userUuid = "{{ Auth::user()->uuid }}";
                    var contractHtml = document.querySelector('.container.mt-5.mb-5.p-5').innerHTML;

                    fetch("/api/sign-terms", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            uuid    : userUuid,
                            sign    : signatureData,
                            term    : 'terms_of_usability',
                            html    : contractHtml
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Contrato Assinado com sucesso!',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '/app';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Não foi possível assinar o contrato!',
                                icon: 'info',
                                timer: 5000
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Não foi possível assinar o contrato!',
                            icon: 'info',
                            timer: 5000
                        });
                    });
                });

            });
        </script>
	</body>
</html>