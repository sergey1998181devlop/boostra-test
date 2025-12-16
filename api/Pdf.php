<?php

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

require_once ROOT . DIRECTORY_SEPARATOR.'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once('Simpla.php');

/**
 * @usage $this->pdf->create(
 *          $this->design->fetch( 'PATH/TO/TEMPLATE_YOU_NEED/FROM"design/boostra_mini_norm/html".tpl' ),
 *          'Уведомление о выплате займа',
 *          'Заявление.pdf',
 *      )
 *
 * @usage $this->init()
 *             ->setFont() see function declaration
 *             ->create()  see previous usage
 */
class Pdf extends Simpla
{
    private $document_author = 'Boostra';
    
    /**
     * @var TCPDF
     */
    private $tcpdf;
    private $pdf_font_family = 'dejavuserif';
    private $pdf_font_style = '';
    private $pdf_font_size = '9';
    private $pdf_font_file = '';
    
    /**
     * Init the TCPDF
     *
     * use Fluent Interface style
     *
     * @param $font
     *
     * @return $this
     */
    public function init()
    {
        $this->tcpdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        return $this;
    }
    
    /**
     * Set font to use in output PDF
     *
     * use Fluent Interface style
     *
     * @param string[] $font Expected keys: family, style, size, file
     *
     * @return $this
     */
    public function setFont( $font = [] )
    {
        $this->pdf_font_family = $font['family'] ?? $this->pdf_font_family;
        $this->pdf_font_style  = $font['style']  ?? $this->pdf_font_style;
        $this->pdf_font_size   = $font['size']   ?? $this->pdf_font_size;
        $this->pdf_font_file   = $font['file']   ?? $this->pdf_font_file;
        
        return $this;
    }
    
    /**
     * Creates a new PDF from given template
     *
     * @param string $template
     * @param string $name
     * @param string $filename
     * @param bool   $save
     * @param array  $options
     *
     *
     * @return void
     */
    public function create($template, $name, $filename,$save = false, $options = [] )
    {
        $this->tcpdf = ! empty( $this->tcpdf )
            ? $this->tcpdf
            : new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $this->tcpdf->SetCreator(PDF_CREATOR);
        $this->tcpdf->SetAuthor($this->document_author);
        $this->tcpdf->SetTitle($name);
        $this->tcpdf->SetSubject('Boostra Document');
        $this->tcpdf->SetKeywords('');
        
        $this->tcpdf->setFont(
            $this->pdf_font_family,
            $this->pdf_font_style,
            $this->pdf_font_size,
            $this->pdf_font_file
        );
        
        $this->tcpdf->SetPrintHeader(false);
        $this->tcpdf->SetPrintFooter(false);

        $this->tcpdf->AddPage();
        
        $this->tcpdf->writeHTML($template, true, false, true, false, '');

        if ($save){
            $filelocation = $options['path'] ?? (ROOT . "/files/asp/");

            if (!is_dir($filelocation)) {
                mkdir($filelocation, 0775, true);
            }

            $fileNL = rtrim($filelocation, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

            $this->tcpdf->Output($fileNL, 'F');
        }else{
            $this->tcpdf->Output($filename, 'I');
        }
    }

    /**
     * Сбрасывает состояние TCPDF
     */
    public function reset(): void
    {
        $this->tcpdf = null;
    }
}