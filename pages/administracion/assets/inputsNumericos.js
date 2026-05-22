"use strict";

(function () {

    const TECLAS_BLOQUEADAS = new Set(['e', 'E', '+', '-']);

    function esInputNumber(elemento) {
        return elemento && elemento.tagName === 'INPUT' && elemento.type === 'number';
    }

    function obtenerLimites(input) {

        const minAttr = input.getAttribute('min');
        const maxAttr = input.getAttribute('max');

        const min = (minAttr === null || minAttr === '') ? 0 : Number(minAttr);
        const max = (maxAttr === null || maxAttr === '') ? null : Number(maxAttr);

        return { min, max };
    }

    
    function ajustarValor(input) {

        if (!esInputNumber(input)) return;
        if (input.value === '' || input.value === '-' || input.value === '+') return;

        const numero = Number(input.value);

        if (Number.isNaN(numero)) {

            input.value = '';
            return;
        }

        const { min, max } = obtenerLimites(input);

        if (min !== null && numero < min) {

            input.value = String(min);
            return;
        }

        if (max !== null && numero > max) {

            input.value = String(max);
            return;
        }
    }

    document.addEventListener('keydown', function (e) {

        const target = e.target;
        if (!esInputNumber(target)) return;

        if (TECLAS_BLOQUEADAS.has(e.key)) {
            e.preventDefault();
        }
    });

    document.addEventListener('paste', function (e) {

        const target = e.target;
        if (!esInputNumber(target)) return;

        setTimeout(() => ajustarValor(target), 0);
    });

    document.addEventListener('input', function (e) { ajustarValor(e.target); });
    document.addEventListener('change', function (e) { ajustarValor(e.target); });
    document.addEventListener('blur', function (e) { ajustarValor(e.target); }, true);

})();
