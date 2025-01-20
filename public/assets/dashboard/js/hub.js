function consulta() {

    let cpfcnpj = document.getElementById('cpfcnpj').value;
    cpfcnpj = cpfcnpj.replace(/\D/g, '');

    let nascimento = document.getElementById('nascimento').value;

    if (cpfcnpj.length === 11) {
        if (nascimento.length < 1) {
            Swal.fire({
                title: 'Atenção',
                text: 'Para PF (CPF) é necessário informar a data de nascimento!',
                icon: 'info',
                timer: 2000
            });

            return;
        }

        consultarCPF(cpfcnpj, nascimento);
    }

    if (cpfcnpj.length > 11) {
        consultarCNPJ(cpfcnpj);
    }
}

function consultarCPF(cpf, nascimento) {

    const url = `https://ws.hubdodesenvolvedor.com.br/v2/cpf/?cpf=${cpf}&data=${nascimento}&token=166681995sgYNDplcRX300939288`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.return == 'OK') {
                Swal.fire({
                    title: 'Validado',
                    text: 'O CPF/CNPJ informado foi válidado!',
                    icon: 'success',
                    timer: 2000
                });

                $('#consultaHub').addClass('d-none');
                $('#formSale').removeClass('d-none');

                $('input[name=name]').val(data.result.nome_da_pf);
                $('input[name=cpfcnpj]').val(data.result.numero_de_cpf);

                let dataNascimento = data.result.data_nascimento;
                if (dataNascimento) {

                    let partesData = dataNascimento.split('/');
                    let dataFormatada = `${partesData[2]}-${partesData[1]}-${partesData[0]}`;
                
                    $('input[name=birth_date]').val(dataFormatada);
                }
            } else {
                Swal.fire({
                    title: 'Atenção',
                    text: 'Não foi possível válidar o CPF/CNPJ informado, verifique os dados com atenção antes de enviar!',
                    icon: 'info',
                    timer: 2000
                });

                $('#consultaHub').addClass('d-none');
                $('#formSale').removeClass('d-none');
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Falha',
                text: 'Não foi possível válidar o CPF/CNPJ informado, verifique os dados com atenção!',
                icon: 'warning',
                timer: 3000
            });

            $('#consultaHub').addClass('d-none');
            $('#formSale').removeClass('d-none');
        });
}

function consultarCNPJ(cnpj) {

    const url = `http://ws.hubdodesenvolvedor.com.br/v2/cnpj/?cnpj=${cnpj}&token=166681995sgYNDplcRX300939288`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.return == 'OK') {

                Swal.fire({
                    title: 'Validado',
                    text: 'O CPF/CNPJ informado foi válidado!',
                    icon: 'success',
                    timer: 2000
                });

                $('#consultaHub').addClass('d-none');
                $('#formSale').removeClass('d-none');
                $('#formRegistrer').removeClass('d-none');

                $('input[name=name]').val(data.result.nome);
                $('input[name=cpfcnpj]').val(data.result.numero_de_inscricao);
            } else {
                Swal.fire({
                    title: 'Inválido',
                    text: 'Não foi possível válidar o CPF/CNPJ informado, verifique os dados com atenção antes de enviar!',
                    icon: 'info',
                    timer: 2000
                });

                $('#consultaHub').addClass('d-none');
                $('#formSale').removeClass('d-none');
                $('#formRegistrer').removeClass('d-none');
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Falha',
                text: 'Não foi possível válidar o CPF/CNPJ informado, verifique os dados com atenção!',
                icon: 'warning',
                timer: 3000
            });

            $('#consultaHub').addClasse('d-none');
            $('#formSale').removeClasse('d-none');
            $('#formRegistrer').removeClass('d-none');
        });
}
