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