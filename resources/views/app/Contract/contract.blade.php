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
            @if (!empty($sale->contract_url))
                {!! $sale->contract_url  !!}
            @else
                {!! $contractContent  !!}

                @if (env('VALUES_FOR_CONTRACT') == true)
                    <div class="table-respondive mb-5">
                        <p>Anexo I</p>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Parcela</th>
                                    <th class="text-center" scope="col">Valor</th>
                                    <th class="text-center" scope="col">Vencimento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <th scope="row">{{ $invoice->description }}</th>
                                        <td class="text-center">R$ {{ number_format($invoice->value, 2, ',', '.') }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
        
        @if (empty($sale->contract_sign))
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
                    var saleId = "{{ $sale->id }}";
                    var contractHtml = document.querySelector('.container.mt-5.mb-5.p-5').innerHTML;

                    fetch("/api/sign-sale", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            id      : saleId,
                            sign    : signatureData,
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
                                window.location.reload();
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