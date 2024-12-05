<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class RunningNumber {
    // ex: 10001
    // function generate($table, $prefix, $position) {
    static function generate($table, $field, $prefix = '') {
        $max = DB::table($table)->max($field);
        $max = \str_replace($prefix, '', $max);

        $currentNumber = (int) $max;
        $nextNumber = $currentNumber+1;

        if($currentNumber < 9){
            $prefix .= "0000";
        }

        if($currentNumber >= 9 && $currentNumber <= 99){
            $prefix .= "000";
        }

        if($currentNumber > 99 && $currentNumber <= 999){
            $prefix .= '00';
        }

        if($currentNumber > 999 && $currentNumber <= 9999){
            $prefix .= '0';
        }

        // if($currentNumber > 9999){
        //     $prefix = $nextNumber;
        // }

        return $prefix . $nextNumber;
    }
}