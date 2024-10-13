<?php
require_once APPPATH . 'libraries/tcpdf/tcpdf.php';
require_once APPPATH . 'libraries/fpdi/src/autoload.php'; // Autoload FPDI classes

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfMerger
{
    protected $pdf;

    public function __construct()
    {
        // Initialize TCPDF with FPDI
        $this->pdf = new Fpdi();
    }

    // Merge multiple PDFs
    public function merge_pdfs($files = [])
    {
        foreach ($files as $file) {
            $pageCount = $this->pdf->setSourceFile($file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $this->pdf->importPage($pageNo);
                $this->pdf->AddPage();
                $this->pdf->useTemplate($tplId);
            }

        }

        // Return merged content
      //  return $this->pdf->Output('S');  // 'S' returns the content as a string

        $pdfContent = $this->pdf->Output('', 'S');  // '' for no filename, 'S' to return as string

       

        return $pdfContent;  // Return the PDF content as a string
    }

    // Save merged PDF to file
    public function save_pdf($content, $output_file)
    {
        file_put_contents($output_file, $content);
    }
}
