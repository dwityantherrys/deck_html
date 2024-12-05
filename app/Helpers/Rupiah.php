<?php

namespace App\Helpers;

class Rupiah {
    public static function format($number) {
        return number_format($number, 2, '.', ',');
    }
}