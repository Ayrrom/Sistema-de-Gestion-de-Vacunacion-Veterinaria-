/**
 * Autocompletado de DNI de propietarios.
 *
 * Uso en un <input>:
 *   data-sugerencias="URL que devuelve [{dni, nombre}]"
 *   data-destino="URL a la que ir al elegir, con __DNI__ donde va el DNI"
 *
 * Mientras se escriben números muestra hasta 10 propietarios cuyo DNI
 * empieza con lo escrito. Se puede elegir con el mouse o con las flechas
 * + Enter; Escape cierra la lista.
 */
(function () {
    'use strict';

    function iniciar(input) {
        // Evita iniciarlo dos veces sobre el mismo campo (dos listas, dos consultas).
        if (input.dataset.sugerenciasActivas) return;
        input.dataset.sugerenciasActivas = '1';

        var urlSugerencias = input.dataset.sugerencias;
        var destino = input.dataset.destino;

        // La lista va al lado del .input-group (no adentro, porque Bootstrap le
        // quitaría las esquinas redondeadas al último elemento del grupo) y se
        // ubica justo debajo de él, con su mismo ancho.
        var grupo = input.closest('.input-group') || input;
        var contenedor = grupo.parentElement;
        contenedor.classList.add('sugerencias-contenedor');

        var lista = document.createElement('div');
        lista.className = 'sugerencias-lista list-group shadow-sm';
        lista.setAttribute('role', 'listbox');
        lista.hidden = true;
        grupo.insertAdjacentElement('afterend', lista);

        function ubicar() {
            lista.style.top = (grupo.offsetTop + grupo.offsetHeight) + 'px';
            lista.style.left = grupo.offsetLeft + 'px';
            lista.style.width = grupo.offsetWidth + 'px';
        }

        input.setAttribute('autocomplete', 'off');
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');

        var temporizador = null;
        var ultimaConsulta = 0;
        var seleccionada = -1;

        function cerrar() {
            lista.hidden = true;
            lista.innerHTML = '';
            seleccionada = -1;
        }

        function ir(dni) {
            window.location.href = destino.replace('__DNI__', encodeURIComponent(dni));
        }

        function marcar(indice) {
            var items = lista.querySelectorAll('.list-group-item');
            items.forEach(function (item, i) {
                item.classList.toggle('active', i === indice);
                item.setAttribute('aria-selected', i === indice ? 'true' : 'false');
            });
            seleccionada = indice;
        }

        function mostrar(sugerencias, escrito) {
            lista.innerHTML = '';
            seleccionada = -1;

            if (sugerencias.length === 0) {
                var vacio = document.createElement('div');
                vacio.className = 'list-group-item text-muted small';
                vacio.textContent = 'Ningún propietario con DNI que empiece con ' + escrito;
                lista.appendChild(vacio);
                ubicar();
                lista.hidden = false;
                return;
            }

            sugerencias.forEach(function (s) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                item.setAttribute('role', 'option');

                // DNI con la parte escrita resaltada (todo con textContent: sin riesgo de XSS).
                var dni = document.createElement('span');
                var coincide = document.createElement('strong');
                coincide.textContent = s.dni.slice(0, escrito.length);
                dni.appendChild(coincide);
                dni.appendChild(document.createTextNode(s.dni.slice(escrito.length)));

                var nombre = document.createElement('span');
                nombre.className = 'text-muted small ms-3 text-truncate';
                nombre.textContent = s.nombre;

                item.appendChild(dni);
                item.appendChild(nombre);

                // mousedown en vez de click: se dispara antes de que el input pierda el foco.
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    ir(s.dni);
                });
                item.dataset.dni = s.dni;
                lista.appendChild(item);
            });

            ubicar();
            lista.hidden = false;
        }

        function buscar() {
            var escrito = input.value.trim();

            // Solo sugiere cuando lo escrito son números (en Propietarios
            // también se puede buscar por nombre y ahí no tiene sentido).
            if (!/^\d{1,8}$/.test(escrito)) {
                cerrar();
                return;
            }

            var numero = ++ultimaConsulta;

            fetch(urlSugerencias + '?dni=' + encodeURIComponent(escrito), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) { return r.ok ? r.json() : []; })
                .then(function (sugerencias) {
                    // Si mientras tanto se siguió escribiendo, se descarta esta respuesta vieja.
                    if (numero === ultimaConsulta && input.value.trim() === escrito) {
                        mostrar(sugerencias, escrito);
                    }
                })
                .catch(cerrar);
        }

        input.addEventListener('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(buscar, 150);
        });

        input.addEventListener('keydown', function (e) {
            var items = lista.querySelectorAll('.list-group-item[data-dni]');
            if (lista.hidden || items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                marcar((seleccionada + 1) % items.length);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                marcar(seleccionada <= 0 ? items.length - 1 : seleccionada - 1);
            } else if (e.key === 'Enter' && seleccionada >= 0) {
                e.preventDefault();
                ir(items[seleccionada].dataset.dni);
            } else if (e.key === 'Escape') {
                cerrar();
            }
        });

        input.addEventListener('blur', function () {
            setTimeout(cerrar, 100);
        });

        input.addEventListener('focus', function () {
            if (input.value.trim() !== '') buscar();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[data-sugerencias]').forEach(iniciar);
    });
})();
