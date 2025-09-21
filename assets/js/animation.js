// animação do "if (talentos) { contrate(); }"
document.addEventListener('DOMContentLoaded', function () {
    const codeText = [
        '<span class="token-keyword">if</span> <span class="token-punctuation">(</span><span class="token-variable">talentos</span><span class="token-punctuation">)</span> <span class="token-punctuation">{</span>\n' +
        '  <span class="token-function">contrate</span><span class="token-punctuation">(</span><span class="token-punctuation">)</span><span class="token-punctuation">;</span>\n' +
        '<span class="token-punctuation">}</span>'
    ];

    var options = {
        strings: codeText,
        typeSpeed: 80,
        backSpeed: 50,
        startDelay: 500,
        showCursor: true,
        contentType: 'html',
    };

    var typed = new Typed('#code-snippet', options);
});

document.addEventListener('DOMContentLoaded', function() {
    const selectorContainer = document.getElementById('user-type-selector');
    const allForms = document.querySelectorAll('.register-form');
    const commonFields = ['nome', 'tel', 'email', 'login', 'senha', 'confirmar_senha'];

    function switchForm(targetId) {
        // Esconde o seletor inicial
        selectorContainer.style.display = 'none';
        
        // Encontra o formulário que está visível no momento (se houver)
        let currentForm = null;
        allForms.forEach(f => {
            if (f.style.display === 'block') {
                currentForm = f;
            }
        });
        
        // Copia os dados dos campos comuns do formulário atual para o de destino
        if (currentForm) {
            const targetForm = document.getElementById(targetId);
            commonFields.forEach(fieldName => {
                const sourceField = currentForm.querySelector(`[name="${fieldName}"]`);
                const destField = targetForm.querySelector(`[name="${fieldName}"]`);
                if (sourceField && destField) {
                    destField.value = sourceField.value;
                }
            });
        }
        
        // Esconde todos os formulários e exibe apenas o de destino
        allForms.forEach(form => {
            form.style.display = form.id === targetId ? 'block' : 'none';
        });
    }

    // Adiciona o evento de clique aos botões de seleção inicial
    document.querySelectorAll('.selection-btn, .form-switcher a').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const targetFormId = this.getAttribute('data-form-target');
            switchForm(targetFormId);
        });
    });

    // Se a página foi recarregada após um erro de submissão, mostra o formulário correto
    const submittedForm = '<?php echo $submitted_form_type; ?>';
    if (submittedForm === 'aluno') {
        switchForm('form-aluno');
    } else if (submittedForm === 'empresa') {
        switchForm('form-empresa');
    }
});