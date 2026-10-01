<?php

namespace App\Support;

class Fmt
{
    /** Montant en francs CFA : 12 500 000 FCFA */
    public static function money($value, string $empty = '—'): string
    {
        if ($value === null || $value === '') {
            return $empty;
        }

        return number_format((float) $value, 0, ',', ' ').' FCFA';
    }

    /** Quantité sans décimales inutiles : 12 ou 12,5 */
    public static function qty($value): string
    {
        $value = (float) $value;

        return floor($value) == $value
            ? number_format($value, 0, ',', ' ')
            : rtrim(rtrim(number_format($value, 2, ',', ' '), '0'), ',');
    }
}
