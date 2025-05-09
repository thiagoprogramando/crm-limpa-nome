function maskCpfCnpj(input) {
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

function maskPhone(phoneInput) {
    let phone = phoneInput.value;
    phone = phone.replace(/\D/g, '');
    phone = phone.replace(/(\d{2})(\d)/, '($1) $2');
    phone = phone.replace(/(\d{5})(\d)/, '$1-$2');
    phoneInput.value = phone;
}

function maskValue(input) {
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

function applyMask(input) {

    let value = input.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/^(\d{3})(\d)/, '$1.$2');
        value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1-$2');
    } else if (value.length <= 14) {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }

    if (input.value.includes('@') || /[a-zA-Z]/.test(input.value)) {
        return;
    }

    input.value = value;
}

function maskPerformance(input) {
    
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


function consultAddress() {
    var postal_code = $('[name="postal_code"]').val();

    postal_code = postal_code.replace(/\D/g, '');
    if (/^\d{8}$/.test(postal_code)) {

        postal_code = postal_code.replace(/(\d{5})(\d{3})/, '$1-$2');
        $.get(`https://viapostal_code.com.br/ws/${postal_code}/json/`, function (data) {
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