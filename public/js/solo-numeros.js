/**
 * Campos que solo aceptan números (por ejemplo el DNI).
 * Uso: <input data-solo-numeros ...>
 *
 * - Al tipear, las letras y otros caracteres directamente no se escriben.
 * - Al pegar o autocompletar, se quitan los caracteres que no son números
 *   (por ejemplo "40.111.222" queda "40111222").
 *
 * Los listeners van en fase de captura sobre document, así se ejecutan antes
 * que los del propio campo (como el autocompletado de DNI) y funcionan
 * también en campos agregados después de cargar la página.
 */
(function () {
    'use strict';

    function esCampoNumerico(el) {
        return el && el.matches && el.matches('input[data-solo-numeros]');
    }

    // Bloquea la tecla antes de que se escriba (navegadores modernos).
    document.addEventListener('beforeinput', function (e) {
        if (!esCampoNumerico(e.target)) return;

        if (e.inputType === 'insertText' && e.data && /\D/.test(e.data)) {
            e.preventDefault();
        }
    }, true);

    // Red de seguridad: pegar, autocompletar del navegador, navegadores viejos.
    document.addEventListener('input', function (e) {
        var campo = e.target;
        if (!esCampoNumerico(campo)) return;

        var limpio = campo.value.replace(/\D/g, '');
        if (limpio === campo.value) return;

        var cursor = campo.selectionStart - (campo.value.length - limpio.length);
        campo.value = limpio;
        try {
            campo.setSelectionRange(Math.max(cursor, 0), Math.max(cursor, 0));
        } catch (err) { /* algunos tipos de input no permiten mover el cursor */ }
    }, true);
})();
