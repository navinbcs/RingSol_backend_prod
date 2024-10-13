<?php

use Mpdf\Mpdf;

class Mpdf_lib
{
    public function __construct($params = [])
    {
        // Manually include the mPDF files
        require_once APPPATH . 'libraries/mpdf/vendor/autoload.php';
    }

    public function load($params = [])
    {
        // Return mPDF object with parameters if provided
        return new Mpdf($params);
    }
}
