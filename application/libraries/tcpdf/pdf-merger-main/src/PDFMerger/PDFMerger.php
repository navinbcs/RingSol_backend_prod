<?php

/**
 *  PDFMerger
 *
 * Class for easily merging PDFs (or specific pages of PDFs) together into one. Output to a file, browser, download, or return as a string.
 * Unfortunately, this class does not preserve many of the enhancements your original PDF might contain. It treats
 * your PDF page as an image and then concatenates them all together.
 *
 * Note that your PDFs are merged in the order that you provide them using the addPDF function, same as the pages.
 * If you put pages 12-14 before 1-5 then 12-15 will be placed first in the output.
 *
 *
 * Uses FPDI ^2.0 from Setasign
 * Uses FPDF 1.8.* from Setasign
 *
 * Both of these packages are free and open source software, bundled with this class for ease of use.
 * They are not modified in any way. PDFMerger has all the limitations of the FPDI package - essentially, it cannot import dynamic content
 * such as form fields, links or page annotations (anything not a part of the page content stream).
 *
 */

namespace SB\PDFMerger;

use setasign\Fpdi\Fpdi;

class PDFMerger
{
    protected $_files;    //['form.pdf']  ["1,2,4, 5-19"]
    protected $_fpdi;

    /**
     * Add a PDF for inclusion in the merge with a valid file path. Pages should be formatted: 1,3,6, 12-16.
     * @param $filepath
     * @param $pages
     * @return void
     */
    public function addPDF($filepath, $pages = 'all', $orientation = null)
    {
        if (file_exists($filepath)) {
            if (strtolower($pages) != 'all') {
                $pages = $this->_rewritepages($pages);
            }

            $this->_files[] = array($filepath, $pages, $orientation);
        } else {
            throw new \Exception("Could not locate PDF on '$filepath'");
        }

        return $this;
    }

    /**
     * Merges your provided PDFs and outputs to specified location.
     * @param $outputmode
     * @param $outputname
     * @param $orientation
     * @return string|bool if $outputmode is 'string' string otherwise true on success, or false on failure.
     * @throws \Exception
     */
    public function merge($outputmode = 'browser', $outputpath = 'newfile.pdf', $orientation = 'A')
    {
        if (!isset($this->_files) || !is_array($this->_files)) {
            throw new \Exception("No PDFs to merge.");
        }

        $this->_fpdi = new Fpdi();

        // merger operations
        foreach ($this->_files as $file) {
            $filename  = $file[0];
            $filepages = $file[1];
            $fileorientation = (!is_null($file[2])) ? $file[2] : $orientation;

            $count = $this->_fpdi->setSourceFile($filename);

            //add the pages
            if ($filepages == 'all') {
                for ($i = 1; $i <= $count; $i++) {
                    $template   = $this->_fpdi->importPage($i);
                    $size       = $this->_fpdi->getTemplateSize($template);
                    if ($fileorientation === 'A') {
                        $fileorientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    }
                    $this->_fpdi->AddPage($fileorientation, array($size['width'], $size['height']));
                    $this->_fpdi->useTemplate($template);
                }
            } else {
                foreach ($filepages as $page) {
                    if (!$template = $this->_fpdi->importPage($page)) {
                        throw new \Exception("Could not load page '$page' in PDF '$filename'. Check that the page exists.");
                    }
                    $size = $this->_fpdi->getTemplateSize($template);

                    $this->_fpdi->AddPage($fileorientation, array($size['width'], $size['height']));
                    $this->_fpdi->useTemplate($template);
                }
            }
        }

        //output operations
        $mode = $this->_switchmode($outputmode);

        if ($mode == 'S') {
            return $this->_fpdi->Output($outputpath, 'S');
        } else {
            if ($this->_fpdi->Output($outputpath, $mode) == '') {
                return true;
            } else {
                throw new \Exception("Error outputting PDF to '$outputmode'.");
                return false;
            }
        }
    }

    /**
     * FPDI uses single characters for specifying the output location. Change our more descriptive string into proper format.
     * @param $mode
     * @return string
     */
    private function _switchmode($mode)
    {
        switch (strtolower($mode)) {
            case 'download':
                return 'D';
                break;
            case 'browser':
                return 'I';
                break;
            case 'file':
                return 'F';
                break;
            case 'string':
                return 'S';
                break;
            default:
                return 'I';
                break;
        }
    }

    /**
     * Takes our provided pages in the form of 1,3,4,16-50 and creates an array of all pages
     * @param $pages
     * @return array
     */
    private function _rewritepages($pages)
    {
        $pages = str_replace(' ', '', $pages);
        $part = explode(',', $pages);

        //parse hyphens
        foreach ($part as $i) {
            $ind = explode('-', $i);

            if (count($ind) == 2) {
                $x = $ind[0]; //start page
                $y = $ind[1]; //end page

                if ($x > $y) {
                    throw new \Exception("Starting page, '$x' is greater than ending page '$y'.");
                    return false;
                }

                //add middle pages
                while ($x <= $y) {
                    $newpages[] = (int) $x;
                    $x++;
                }
            } else {
                $newpages[] = (int) $ind[0];
            }
        }

        return $newpages;
    }
}