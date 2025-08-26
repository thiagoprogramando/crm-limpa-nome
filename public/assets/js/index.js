document.addEventListener('DOMContentLoaded', function () {
    applyMasks(document);
    document.querySelectorAll('form.delete').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            Swal.fire({
                title: 'Tem certeza?',
                text: 'Você realmente deseja excluir este registro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                confirmButtonColor: '#008000',
                cancelButtonText: 'Não',
                cancelButtonColor: '#FF0000',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});

$(document).on('shown.bs.modal', '.modal', function () {
    applyMasks(this);
});

function applyMasks(context) {
    context.querySelectorAll('.money').forEach(el => el.value && maskValue(el));
    context.querySelectorAll('.performance').forEach(el => el.value && maskPerformance(el));
    context.querySelectorAll('.phone').forEach(el => el.value && maskPhone(el));
    context.querySelectorAll('.cpfcnpj').forEach(el => el.value && maskCpfCnpj(el));
    context.querySelectorAll('.address').forEach(el => el.value && consultAddress(el));
}

function onClip(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            title: 'Sucesso!',
            text: 'Copiado',
            icon: 'success',
            timer: 5000
        });
    }).catch(err => {
        Swal.fire({
            title: 'Erro!',
            text: 'Nada copiado, tente novamente!',
            icon: 'error',
            timer: 5000
        });
    });
}