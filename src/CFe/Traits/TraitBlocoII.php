<?php

namespace NFePHP\DA\CFe\Traits;

/**
 * Bloco sub cabecalho com a identificação e logo do emitente
 */
trait TraitBlocoII
{
    protected function blocoII($y)
    {
        //$this->bloco2H = 12;
        //$aFont = ['font'=> $this->fontePadrao, 'size' => 7, 'style' => ''];
        //$this->pdf->textBox($this->margem, $y, $this->wPrint, $this->bloco2H, '', $aFont, 'T', 'C', true, '', false);
        $texto = "Extrato Nº: " . $this->nCFe  . "\nCupom Fiscal Eletrônico - SAT";
        $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];
        $y1 = $this->pdf->textBox(
            $this->margem,
            $this->bloco1H - 2,
            $this->wPrint,
            $this->bloco2H,
            $texto,
            $aFont,
            'C',
            'C',
            false,
            '',
            true
        );

        $this->pdf->dashedHLine($this->margem, $this->bloco2H + $y, $this->wPrint, 0.1, 30);
        return $this->bloco2H + $y;
    }
}
