<?php

namespace App\Helpers;

use NumberFormatter;

class NumeroHelper
{
    public static function convertirALetras($numero)
    {
        $formatter = new NumberFormatter("es", NumberFormatter::SPELLOUT);
        $letras = $formatter->format($numero);

        return ucfirst($letras) . ' lempiras exactos';
    }
}