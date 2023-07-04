<?php

namespace NFePHP\DA\CFe\Traits;

/**
 * Bloco VI informações de chave de acesso
 */
trait TraitBlocoVI
{
    protected function blocoVI($y)
    {
        //$this->bloco6H = 10;

        //$aFont = ['font'=> $this->fontePadrao, 'size' => 7, 'style' => ''];
        //$this->pdf->textBox($this->margem, $y, $this->wPrint, $this->bloco6H, '', $aFont, 'T', 'C', false, '', false);

        $nome = $this->getTagValue($this->dest, "xNome") ?: "XXX";
        $cnpj = $this->getTagValue($this->dest, "CNPJ");
        $cpf = $this->getTagValue($this->dest, "CPF");
        $rua = $this->getTagValue($this->enderDest, "xLgr");
        $numero = $this->getTagValue($this->enderDest, "nro");
        $complemento = $this->getTagValue($this->enderDest, "xCpl");
        $bairro = $this->getTagValue($this->enderDest, "xBairro");
        $mun = $this->getTagValue($this->enderDest, "xMun");
        $uf = $this->getTagValue($this->enderDest, "UF");
        $texto = '';
        $yPlus = 0;
        if (!empty($cnpj)) {
            $texto = "CONSUMIDOR - CNPJ XX.XXX.XXX/" . $this->formatField($cnpj, "####-##") . "\nRazão Social: " . $nome;
        } elseif (!empty($cpf)) {
            $texto = "CONSUMIDOR - CPF XXX.XXX.XX" . $this->formatField($cpf, "#-##") . "\nNome: " . $nome;
        } else {
            $texto = 'CONSUMIDOR NÃO IDENTIFICADO';
        }
        if (!empty($rua)) {
            $texto .= "\n {$rua}, {$numero} {$complemento} {$bairro} {$mun}-{$uf}";
        }
        if ($this->getTagValue($this->nfeProc, "xMsg")) {
            $texto .= "\n {$this->getTagValue($this->nfeProc, "xMsg")}";
            $this->bloco6H += 4;
        }
        $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];
        $y1 = $this->pdf->textBox(
            $this->margem,
            $y + 1,
            $this->wPrint,
            $this->bloco6H,
            $texto,
            $aFont,
            'T',
            'L',
            false,
            '',
            false
        );
        $this->pdf->dashedHLine($this->margem, $this->bloco6H + $y, $this->wPrint, 0.1, 30);
        return $this->bloco6H + $y;
    }
}
