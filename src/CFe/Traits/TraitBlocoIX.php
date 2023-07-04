<?php

namespace NFePHP\DA\CFe\Traits;

/**
 * Bloco Informações sobre impostos aproximados
 */
trait TraitBlocoIX
{
    protected function blocoIX($y)
    {
        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
        $valor = $this->getTagValue($this->imposto, 'vTotTrib');
        $trib = !empty($valor) ? number_format((float) $valor, 2, ',', '.') : '0,00';
        $texto = "Valor aproximado dos tributos (Lei Federal 12.741/2012): R$ {$trib}";
        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
        $y1 = $this->pdf->textBox(
            $this->margem,
            $y + 1,
            $this->wPrint,
            $this->bloco9H,
            $texto,
            $aFont,
            'T',
            'C',
            false,
            '',
            true
        );

        $texto = "OBSERVÇÕES DO CONTRIBUINTE";
        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
        $y2 = $this->pdf->textBox(
            $this->margem,
            $y + $y1 + 4,
            $this->wPrint,
            $this->bloco9H,
            $texto,
            $aFont,
            'T',
            'C',
            false,
            '',
            false
        );
        if ($this->paperwidth < 70) {
            $fsize = 5;
            $aFont = ['font' => $this->fontePadrao, 'size' => 5, 'style' => ''];
        }
        $this->pdf->textBox(
            $this->margem,
            $y + $y1 + $y2 + 4,
            $this->wPrint,
            $this->bloco9H - 4,
            $this->infCpl,
            $aFont,
            'T',
            'L',
            false,
            '',
            false
        );
        return $this->bloco9H + $y;
    }

    /**
     * Calcula a altura do bloco IX
     * Depende do conteudo de infCpl
     *
     * @return int
     */
    protected function calculateHeighBlokIX()
    {
        $papel = [$this->paperwidth, 100];
        $wprint = $this->paperwidth - (2 * $this->margem);
        $logoAlign = 'L';
        $orientacao = 'P';
        $pdf = new \NFePHP\DA\Legacy\Pdf($orientacao, 'mm', $papel);
        $fsize = 7;
        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
        if ($this->paperwidth < 70) {
            $fsize = 5;
            $aFont = ['font' => $this->fontePadrao, 'size' => 5, 'style' => ''];
        }
        $linhas = $this->infCpl;
        $hfont = (imagefontheight($fsize) / 72) * 13;
        $numlinhas = $pdf->getNumLines($linhas, $wprint, $aFont) + 2;
        return (int) ($numlinhas * $hfont) + 2;
    }
}
