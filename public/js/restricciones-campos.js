/**
 * Restricción de caracteres en los campos de los formularios.
 * Los atributos los genera App\Support\Campos::atributos() en el servidor.
 *
 *   data-solo-numeros        solo dígitos (DNI, teléfono, altura, edad...)
 *   data-solo-decimal="N"    número con hasta N decimales; la coma se
 *                            convierte en punto (peso, temperatura)
 *   data-solo-letras         letras (con tildes y ñ), espacios, ' . -
 *
 * - Al tipear, los caracteres no permitidos directamente no se escriben.
 * - Al pegar o autocompletar se quitan (ej.: "40.111.222" queda "40111222").
 * - El máximo de caracteres lo pone el maxlength del propio campo.
 *
 * Los listeners van en fase de captura sobre document: se ejecutan antes que
 * los del propio campo (como el autocompletado de DNI) y sirven también para
 * campos que se agreguen después de cargar la página. El servidor valida lo
 * mismo, así que esto es una ayuda, no la única protección.
 */
(function () {
    'use strict';

    var LETRA = /[\p{L}\s'.\-]/u;

    function tipoDe(campo) {
        if (!campo || !campo.matches) return null;
        if (campo.matches('[data-solo-numeros]')) return 'numeros';
        if (campo.matches('[data-solo-decimal]')) return 'decimal';
        if (campo.matches('[data-solo-letras]')) return 'letras';
        return null;
    }

    /** Devuelve el valor dejando solo lo permitido para el tipo de campo. */
    function limpiar(campo, valor) {
        switch (tipoDe(campo)) {
            case 'numeros':
                return valor.replace(/\D/g, '');
            case 'letras':
                return Array.from(valor).filter(function (c) { return LETRA.test(c); }).join('');
            case 'decimal':
                var decimales = parseInt(campo.dataset.soloDecimal, 10);
                if (isNaN(decimales)) decimales = 2;
                var limpio = valor.replace(/,/g, '.').replace(/[^\d.]/g, '');
                var partes = limpio.split('.');
                var entera = partes.shift();
                if (partes.length === 0) return entera;
                var decimal = partes.join('').slice(0, decimales);
                return decimales === 0 ? entera : entera + '.' + decimal;
        }
        return valor;
    }

    // Bloquea la tecla antes de que se escriba (navegadores modernos).
    document.addEventListener('beforeinput', function (e) {
        var campo = e.target;
        if (!tipoDe(campo) || e.inputType !== 'insertText' || !e.data) return;

        // Cómo quedaría el valor con la tecla nueva
        var inicio = campo.selectionStart ?? campo.value.length;
        var fin = campo.selectionEnd ?? campo.value.length;
        var nuevo = campo.value.slice(0, inicio) + e.data + campo.value.slice(fin);

        if (limpiar(campo, nuevo) !== nuevo.replace(/,/g, tipoDe(campo) === 'decimal' ? '.' : ',')) {
            e.preventDefault();
        }
    }, true);

    // Red de seguridad: pegar, autocompletar, la coma decimal, navegadores viejos.
    document.addEventListener('input', function (e) {
        var campo = e.target;
        if (!tipoDe(campo)) return;

        var limpio = limpiar(campo, campo.value);
        if (limpio === campo.value) return;

        var cursor = (campo.selectionStart ?? campo.value.length) - (campo.value.length - limpio.length);
        campo.value = limpio;
        try {
            campo.setSelectionRange(Math.max(cursor, 0), Math.max(cursor, 0));
        } catch (err) { /* algunos tipos de input no permiten mover el cursor */ }
    }, true);
})();
