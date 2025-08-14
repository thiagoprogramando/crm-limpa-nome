function mascaraCpfCnpj(input) {
    let value = input.value;
    value = value.replace(/\D/g, '');

    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }

    input.value = value;
}

function mascaraData(dataInput) {
    let data = dataInput.value;
    data = data.replace(/\D/g, '');
    data = data.replace(/(\d{2})(\d)/, '$1-$2')
    data = data.replace(/(\d{2})(\d)/, '$1-$2');
    dataInput.value = data;
}

function mascaraTelefone(telefoneInput) {
    let telefone = telefoneInput.value;
    telefone = telefone.replace(/\D/g, '');
    telefone = telefone.replace(/(\d{2})(\d)/, '($1) $2');
    telefone = telefone.replace(/(\d{5})(\d)/, '$1-$2');
    telefoneInput.value = telefone;
}

function mascaraReal(input) {
    let value = input.value;
    
    if (value === '') {
        input.value = '0,00';
        return;
    }
    
    value = value.replace(/\D/g, '');
    value = (parseInt(value) / 100).toFixed(2);
    value = value.replace('.', ',');
    value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');

    input.value = value;
}

function mascaraPorcentagem(input) {
    let value = input.value;

    value = value.replace(/[^\d.]/g, '');
    value = value.replace(/(\..*)\./g, '$1');

    if (value.includes('.')) {
        const parts = value.split('.');
        value = parts[0].replace(/\D/g, '') + '.' + parts.slice(1).join('');
    } else {
        value = value.replace(/\D/g, '');
    }

    input.value = value;
}

function consultaCEP() {
    var cep = $('[name="postal_code"]').val();

    cep = cep.replace(/\D/g, '');

    if (/^\d{8}$/.test(cep)) {

        cep = cep.replace(/(\d{5})(\d{3})/, '$1-$2');
        $.get(`https://viacep.com.br/ws/${cep}/json/`, function (data) {
            $('[name="address"]').val(data.logradouro);
            $('[name="complement"]').val(data.bairro);
            $('[name="city"]').val(data.localidade);
            $('[name="state"]').val(data.uf);
        })
        .fail(function () {
            Swal.fire({
                title: 'Error!',
                text: 'CEP não localizado!',
                icon: 'error',
                timer: 1500
            })
        });
    } else {
        Swal.fire({
            title: 'Error!',
            text: 'CEP inválido!',
            icon: 'error',
            timer: 1500
        })
    }
}