<?php
namespace App\Helpers;

class PrintFile {
    public static function original ($file, $data, $fileName)
    {
        $pdf = \PDF::loadView($file, array_merge(['isCopy' => false], $data));  
        return $pdf->download($fileName . '.pdf');
    }
    public static function copy ($file, $data, $fileName)
    {
        $pdf = \PDF::loadView($file, array_merge(['isCopy' => true], $data));  
        return $pdf->download($fileName . '-copy.pdf');
    }
}