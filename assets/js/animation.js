document.addEventListener('DOMContentLoaded', function () {
    const codeText = [
        '<span class="token-keyword">if</span> <span class="token-punctuation">(</span><span class="token-variable">talentos</span><span class="token-punctuation">)</span> <span class="token-punctuation">{</span>\n' +
        '  <span class="token-function">contrate</span><span class="token-punctuation">(</span><span class="token-punctuation">)</span><span class="token-punctuation">;</span>\n' +
        '<span class="token-punctuation">}</span>'
    ];

    var options = {
        strings: codeText,
        typeSpeed: 80, // Velocidade da digitação
        backSpeed: 50, // Velocidade ao apagar
        startDelay: 500,
        showCursor: true,
        contentType: 'html', // Importante para renderizar os <span>
    };

    var typed = new Typed('#code-snippet', options);
});