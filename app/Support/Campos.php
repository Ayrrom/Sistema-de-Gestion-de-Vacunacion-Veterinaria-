<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Definición única de cada campo de texto/número de los formularios: qué
 * caracteres acepta y cuántos como máximo. De acá salen:
 *  - los atributos del <input> (maxlength, teclado del celular, filtro de
 *    caracteres en public/js/restricciones-campos.js) -> Campos::atributos()
 *  - las reglas de validación del servidor          -> Campos::reglas()
 * Así el límite de la pantalla y el del servidor no se pueden desincronizar.
 */
class Campos
{
    /** Solo dígitos. */
    public const NUMEROS = 'numeros';

    /** Número con decimales (se acepta coma o punto). */
    public const DECIMAL = 'decimal';

    /** Letras (con tildes y ñ), espacios, apóstrofo, punto y guion. */
    public const LETRAS = 'letras';

    /** Texto libre (letras, números y signos). */
    public const TEXTO = 'texto';

    /** Expresión de LETRAS para el servidor. */
    private const REGEX_LETRAS = "/^[\\pL\\s'.\\-]+$/u";

    /**
     * campo => [tipo, máximo de caracteres, extras]
     * extras: min/max (valor numérico), decimales, min_largo (mínimo de caracteres).
     */
    public const DEFINICIONES = [
        // Personas
        'dni' => [self::NUMEROS, 8],
        'name' => [self::LETRAS, 100],
        'catedra' => [self::TEXTO, 100],

        // Propietario
        'nombre_propietario' => [self::LETRAS, 50],
        'apellido' => [self::LETRAS, 50],
        'telefono' => [self::NUMEROS, 15, ['min_largo' => 6]],
        'calle' => [self::TEXTO, 100],
        'altura' => [self::NUMEROS, 5],
        'localidad' => [self::LETRAS, 60],

        // Paciente
        'nombre_mascota' => [self::LETRAS, 50],
        'raza' => [self::LETRAS, 50],
        'tipo_pelaje' => [self::LETRAS, 30],
        'color' => [self::LETRAS, 30],
        'procedencia' => [self::TEXTO, 100],
        'senas_particulares' => [self::TEXTO, 500],
        'peso' => [self::DECIMAL, 6, ['min' => 0.01, 'max' => 999.99, 'decimales' => 2]],
        'edad' => [self::NUMEROS, 2, ['min' => 0, 'max' => 40]],

        // Ficha clínica
        'motivo_consulta' => [self::TEXTO, 255],
        'vacunas_aplicadas' => [self::TEXTO, 500],
        'droga_desparasitacion' => [self::TEXTO, 100],
        'producto_desparasitacion' => [self::TEXTO, 100],
        'fc' => [self::NUMEROS, 3, ['min' => 0, 'max' => 400]],
        'fr' => [self::NUMEROS, 3, ['min' => 0, 'max' => 200]],
        'temperatura' => [self::DECIMAL, 4, ['min' => 30, 'max' => 45, 'decimales' => 1]],
        'pulso' => [self::TEXTO, 50],
        'tllc' => [self::TEXTO, 20],
        'relacion_entorno' => [self::TEXTO, 100],
    ];

    /** Máximo de caracteres de un campo. */
    public static function max(string $campo): int
    {
        return self::DEFINICIONES[$campo][1];
    }

    /**
     * Atributos HTML para el <input>/<textarea>: maxlength, teclado del
     * celular y la marca que usa el filtro de caracteres.
     */
    public static function atributos(string $campo): HtmlString
    {
        [$tipo, $max, $extras] = self::DEFINICIONES[$campo] + [2 => []];

        $atributos = ['maxlength="' . $max . '"'];

        switch ($tipo) {
            case self::NUMEROS:
                $atributos[] = 'inputmode="numeric" pattern="[0-9]*" title="Solo números" data-solo-numeros';
                break;
            case self::DECIMAL:
                $atributos[] = 'inputmode="decimal" title="Solo números (con coma o punto)" data-solo-decimal="' . ($extras['decimales'] ?? 2) . '"';
                break;
            case self::LETRAS:
                $atributos[] = 'title="Solo letras" data-solo-letras';
                break;
        }

        return new HtmlString(implode(' ', $atributos));
    }

    /**
     * Reglas de validación del servidor para el campo.
     *
     * @param  bool  $obligatorio  required o nullable
     */
    public static function reglas(string $campo, bool $obligatorio = false): array
    {
        [$tipo, $max, $extras] = self::DEFINICIONES[$campo] + [2 => []];

        $reglas = [$obligatorio ? 'required' : 'nullable'];

        switch ($tipo) {
            case self::NUMEROS:
                $reglas[] = 'string';
                $reglas[] = 'regex:/^[0-9]+$/';
                $reglas[] = 'max:' . $max;
                if (isset($extras['min_largo'])) {
                    $reglas[] = 'min:' . $extras['min_largo'];
                }
                if (isset($extras['min'], $extras['max'])) {
                    // Se valida el valor numérico además de los dígitos.
                    $reglas = [$obligatorio ? 'required' : 'nullable', 'integer', 'min:' . $extras['min'], 'max:' . $extras['max']];
                }
                break;
            case self::DECIMAL:
                $reglas[] = 'numeric';
                $reglas[] = 'decimal:0,' . ($extras['decimales'] ?? 2);
                $reglas[] = 'min:' . $extras['min'];
                $reglas[] = 'max:' . $extras['max'];
                break;
            case self::LETRAS:
                $reglas[] = 'string';
                $reglas[] = 'max:' . $max;
                $reglas[] = 'regex:' . self::REGEX_LETRAS;
                break;
            default:
                $reglas[] = 'string';
                $reglas[] = 'max:' . $max;
        }

        return $reglas;
    }

    /**
     * Mensajes en español para las reglas de un campo.
     *
     * @param  string  $nombreVisible  cómo se llama el campo para el usuario ("El teléfono")
     */
    public static function mensajes(string $campo, string $nombreVisible, ?string $clave = null): array
    {
        [$tipo, $max, $extras] = self::DEFINICIONES[$campo] + [2 => []];
        $clave ??= $campo;

        $mensajes = [
            "{$clave}.max" => "{$nombreVisible} puede tener como máximo {$max} caracteres.",
            "{$clave}.regex" => match ($tipo) {
                self::NUMEROS => "{$nombreVisible} solo puede tener números.",
                self::LETRAS => "{$nombreVisible} solo puede tener letras.",
                default => "{$nombreVisible} no es válido.",
            },
        ];

        if (isset($extras['min_largo'])) {
            $mensajes["{$clave}.min"] = "{$nombreVisible} tiene que tener al menos {$extras['min_largo']} números.";
        }
        if (isset($extras['min'], $extras['max'])) {
            $mensajes["{$clave}.min"] = "{$nombreVisible} tiene que estar entre {$extras['min']} y {$extras['max']}.";
            $mensajes["{$clave}.max"] = "{$nombreVisible} tiene que estar entre {$extras['min']} y {$extras['max']}.";
            $mensajes["{$clave}.integer"] = "{$nombreVisible} tiene que ser un número entero.";
            $mensajes["{$clave}.numeric"] = "{$nombreVisible} tiene que ser un número.";
            $mensajes["{$clave}.decimal"] = "{$nombreVisible} puede tener como máximo " . ($extras['decimales'] ?? 2) . ' decimales.';
        }

        return $mensajes;
    }
}
