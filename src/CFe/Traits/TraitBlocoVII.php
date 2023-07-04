<?php

namespace NFePHP\DA\CFe\Traits;

/**
 * Bloco VII informações do consumidor e dados da NFCe
 */
trait TraitBlocoVII
{
    protected function blocoVII($y)
    {
        $num = str_pad($this->getTagValue($this->ide, "nCFe"), 9, '0', STR_PAD_LEFT);
        $serie = str_pad($this->getTagValue($this->ide, "nserieSAT"), 3, '0', STR_PAD_LEFT);
        $texto = "CFe Nº {$num} Série {$serie}";
        $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];
        $y1 = $this->pdf->textBox(
            $this->margem,
            $y + 1,
            $this->wPrint,
            4,
            $texto,
            $aFont,
            'T',
            'C',
            false,
            '',
            true
        );

        $chave =  str_replace('CFe', '', $this->infCFe->getAttribute("Id"));
        $texto = $this->formatField($chave, $this->formatoChave);
        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
        $y2 = $this->pdf->textBox(
            $this->margem,
            $y + $y1 + 1,
            $this->wPrint,
            2,
            $texto,
            $aFont,
            'T',
            'C',
            false,
            '',
            true
        );

        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
        $texto = (new \DateTime($this->getTagValue($this->ide, "dEmi") . " " . $this->getTagValue($this->ide, "hEmi")))->format('d/m/Y H:i:s');
        $y3 = $this->pdf->textBox(
            $this->margem,
            $y + $y1 + $y2 + 1,
            $this->wPrint,
            4,
            $texto,
            $aFont,
            'T',
            'C',
            false,
            '',
            true
        );

        // $this->blocoVIIProt($y + 1 + $y1 + $y2 + $y3, $subSize, $protocolo, $dhRecbto);

        $this->pdf->dashedHLine($this->margem, $this->bloco7H + $y, $this->wPrint, 0.1, 30);
        return $this->bloco7H + $y;
    }

    protected function blocoVIIProt($y, $subSize, $protocolo, $dhRecbto)
    {
        $texto = "Protocolo de Autorização:  {$protocolo}";
        $aFont = ['font' => $this->fontePadrao, 'size' => (8 - $subSize), 'style' => ''];
        $y1 = $this->pdf->textBox(
            $this->margem,
            $y,
            $this->wPrint,
            4,
            $texto,
            $aFont,
            'T',
            'C',
            false,
            '',
            true
        );

        $texto = "Data de Autorização:  {$dhRecbto}";
        $aFont = ['font' => $this->fontePadrao, 'size' => (8 - $subSize), 'style' => ''];
        return $this->pdf->textBox(
            $this->margem,
            $y + $y1,
            $this->wPrint,
            4,
            $texto,
            $aFont,
            'T',
            'C',
            false,
            '',
            true
        );
    }
}
