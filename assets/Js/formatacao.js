document.addEventListener("DOMContentLoaded", function () {
    const cpfInput = document.querySelector(".cpf-mask");
    const telInput = document.querySelector(".telefone-mask");
    const form = document.querySelector("form");

    // Formatação do CPF e Telefone antes de enviar
    form.addEventListener("submit", function (e) {
        let cpf = cpfInput.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
        let telefone = telInput.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
        
        // Reaplica a formatação do CPF
        cpfInput.value = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        // Reaplica a formatação do telefone
        telInput.value = telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        
        // Agora os dados estão formatados, podemos deixar o formulário ser enviado
        return true;
    });

    // Aplique a formatação de entrada enquanto o usuário digita (não afeta o submit diretamente)
    cpfInput.addEventListener("input", () => {
        let value = cpfInput.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        cpfInput.value = value;
    });

    telInput.addEventListener("input", () => {
        let value = telInput.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        value = value.replace(/^(\d{2})(\d)/g, "($1) $2");
        value = value.replace(/(\d{5})(\d{1,4})$/, "$1-$2");
        telInput.value = value;
    });
});
