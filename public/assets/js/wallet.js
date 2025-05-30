document.getElementById('submitBtn').addEventListener('click', function() {
    const key = document.getElementById('floatingKey').value;
    const value = document.getElementById('floatingValue').value;
    const type = document.getElementById('floatingType').value;

    Swal.fire({
        title: 'Confirmação',
        html: `<p>Chave Pix: ${key}</p>
                <p>Valor: ${value}</p>
                <p>Tipo: ${type}</p>
                <p>Deseja confirmar?</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('withdrawForm').submit();
        }
    });
});