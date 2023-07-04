<?php

namespace NFePHP\DA\CFe\Traits;

/**
 * Bloco totais da NFCe
 */
trait TraitBlocoIV
{
    protected function blocoIV($y)
    {
        //$this->bloco4H = 13;

        //$aFont = ['font'=> $this->fontePadrao, 'size' => 7, 'style' => ''];
        //$this->pdf->textBox($this->margem, $y, $this->wPrint, $this->bloco4H, '', $aFont, 'T', 'C', true, '', false);

        $qtd = $this->det->length;
        $valor = $this->getTagValue($this->total, 'vProd') ?: 0;
        $desconto = $this->getTagValue($this->total, 'vDesc') ?: 0;
        $bruto = $this->getTagValue($this->total, 'vCFe') ?: 0;

        $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => ''];
        $texto = "Qtde total de itens";
        $this->pdf->textBox(
            $this->margem,
            $y,
            $this->wPrint / 2,
            3,
            $texto,
            $aFont,
            'T',
            'L',
            false,
            '',
            false
        );
        $y1 = $this->pdf->textBox(
            $this->margem + $this->wPrint / 2,
            $y,
            $this->wPrint / 2,
            3,
            $qtd,
            $aFont,
            'T',
            'R',
            false,
            '',
            false
        );

        $texto = "Valor Total R$";
        $this->pdf->textBox(
            $this->margem,
            $y + $y1,
            $this->wPrint / 2,
            3,
            $texto,
            $aFont,
            'T',
            'L',
            false,
            '',
            false
        );
        $texto = number_format((float) $bruto, 2, ',', '.');
        $y2 = $this->pdf->textBox(
            $this->margem + $this->wPrint / 2,
            $y + $y1,
            $this->wPrint / 2,
            3,
            $texto,
            $aFont,
            'T',
            'R',
            false,
            '',
            false
        );

        $texto = "Desconto R$";
        $this->pdf->textBox(
            $this->margem,
            $y + $y1 + $y2,
            $this->wPrint / 2,
            3,
            $texto,
            $aFont,
            'T',
            'L',
            false,
            '',
            false
        );
        $texto = number_format((float) $desconto, 2, ',', '.');
        $y3 = $this->pdf->textBox(
            $this->margem + $this->wPrint / 2,
            $y + $y1 + $y2,
            $this->wPrint / 2,
            3,
            $texto,
            $aFont,
            'T',
            'R',
            false,
            '',
            false
        );

        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => 'B'];
        $texto = "Valor a Pagar R$";
        $this->pdf->textBox(
            $this->margem,
            $y + $y1 + $y2 + $y3 + $y4,
            $this->wPrint / 2,
            3,
            $texto,
            $aFont,
            'T',
            'L',
            false,
            '',
            false
        );
        $texto = number_format((float) $valor, 2, ',', '.');
        $y4 = $this->pdf->textBox(
            $this->margem + $this->wPrint / 2,
            $y + $y1 + $y2 + $y3 + $y4,
            $this->wPrint / 2,
            3,
            $texto,
            $aFont,
            'T',
            'R',
            false,
            '',
            false
        );

        // $this->pdf->dashedHLine($this->margem, $this->bloco4H + $y, $this->wPrint, 0.1, 30);
        return $this->bloco4H + $y;
    }
}
