<?php

namespace NFePHP\DA\CFe;

/**
 * Classe para a impressão em PDF do Documento Auxiliar de SAT/CFe
 *
 * @category  Library
 * @package   nfephp-org/sped-da
 * @copyright 2009-2020 NFePHP
 * @license   http://www.gnu.org/licenses/lesser.html LGPL v3
 * @link      http://github.com/nfephp-org/sped-da for the canonical source repository
 */

use DateTime;
use Exception;
use InvalidArgumentException;
use NFePHP\DA\Legacy\Dom;
use NFePHP\DA\Legacy\Pdf;
use NFePHP\DA\Common\DaCommon;
use Com\Tecnick\Barcode\Barcode;

class Dacfe extends DaCommon
{
    protected $papel;
    protected $paperwidth = 80;
    protected $descPercent = 0.38;
    protected $xml; // string XML NFe
    protected $dom;
    protected $logomarca = ''; // path para logomarca em jpg
    protected $formatoChave = "#### #### #### #### #### #### #### #### #### #### ####";
    protected $nfeProc;
    protected $nfe;
    protected $infCFe;
    protected $ide;
    protected $enderDest;
    protected $total;
    protected $imposto;
    protected $emit;
    protected $enderEmit;
    protected $qrCode;
    protected $urlChave;
    protected $det;
    protected $infAdic;
    protected $infCpl;
    protected $textoAdic;
    protected $tpEmis;
    protected $tpAmb;
    protected $nCFe;
    protected $pag;
    protected $itens = [];
    protected $dest;
    protected $imgQRCode;
    protected $urlQR = '';
    protected $pdf;
    protected $margem = 2;
    protected $flagResume = false;
    protected $hMaxLinha = 5;
    protected $hBoxLinha = 6;
    protected $hLinha = 3;
    protected $aFontTit = ['font' => 'times', 'size' => 9, 'style' => 'B'];
    protected $aFontTex = ['font' => 'times', 'size' => 8, 'style' => ''];
    protected $via = "Via Consumidor";
    protected $offline_double = true;
    protected $canceled = false;
    protected $submessage = null;

    protected $bloco1H = 18.0; //cabecalho
    protected $bloco2H = 12.0; //informação fiscal

    protected $bloco3H = 0.0; //itens
    protected $bloco4H = 12.0; //totais
    protected $bloco5H = 0.0; //formas de pagamento

    protected $bloco6H = 8.0; //informação do consumidor
    protected $bloco7H = 10.0; //informações para consulta
    protected $bloco8H = 55.0; //qrcode
    protected $bloco9H = 4.0; //informações sobre tributos
    protected $bloco10H = 5.0; //informações do integrador

    use Traits\TraitBlocoI;
    use Traits\TraitBlocoII;
    use Traits\TraitBlocoIII;
    use Traits\TraitBlocoIV;
    use Traits\TraitBlocoV;
    use Traits\TraitBlocoVI;
    use Traits\TraitBlocoVII;
    use Traits\TraitBlocoVIII;
    use Traits\TraitBlocoIX;
    use Traits\TraitBlocoX;

    /**
     * Construtor
     *
     * @param string $xml
     *
     * @throws Exception
     */
    public function __construct($xml)
    {
        $this->xml = $xml;
        if (empty($xml)) {
            throw new \Exception('Um xml de CFe deve ser passado ao construtor da classe.');
        }
        //carrega dados do xml
        $this->loadXml();
    }

    /**
     * Seta a largura do papel de impressão em mm
     *
     * @param int $width
     */
    public function setPaperWidth($width = 80)
    {
        if ($width < 58) {
            throw new Exception("Largura insuficiente para a impressão do documento");
        }
        $this->paperwidth = $width;
    }

    /**
     * Seta margens de impressão em mm
     *
     * @param int $width
     */
    public function setMargins($width = 2)
    {
        if ($width > 4 || $width < 0) {
            throw new Exception("As margens devem estar entre 0 e 4 mm.");
        }
        $this->margem = $width;
    }

    /**
     * Seta a fonte a ser usada times ou arial
     *
     * @param string $font
     */
    public function setFont($font = 'times')
    {
        if (!in_array($font, ['times', 'arial'])) {
            $this->fontePadrao = 'times';
        } else {
            $this->fontePadrao = $font;
        }
    }

    /**
     * Seta a impressão para NFCe completa ou Simplificada
     *
     * @param bool $flag
     */
    public function setPrintResume($flag = false)
    {
        $this->flagResume = $flag;
    }

    /**
     * Marca como cancelada
     */
    public function setAsCanceled()
    {
        $this->canceled = true;
    }

    /**
     * Registra via do estabelecimento quando a impressção for offline
     */
    public function setViaEstabelecimento()
    {
        $this->via = "Via Estabelecimento";
    }

    /**
     * Habilita a impressão de duas vias quando NFCe for OFFLINE
     *
     * @param bool $flag
     */
    public function setOffLineDoublePrint($flag = true)
    {
        $this->offline_double = $flag;
    }

    /**
     * Renderiza o pdf
     *
     * @param string $logo
     * @return string
     */
    public function render($logo = '')
    {
        $this->monta($logo);
        //$this->papel = 80;
        return $this->pdf->getPdf();
    }

    protected function monta(
        $logo = ''
    ) {
        if (!empty($logo)) {
            $this->logomarca = $this->adjustImage($logo, true);
        }
        $tamPapelVert = $this->calculatePaperLength();
        $this->orientacao = 'P';
        $this->papel = [$this->paperwidth, $tamPapelVert];
        $this->logoAlign = 'L';
        $this->pdf = new Pdf($this->orientacao, 'mm', $this->papel);

        //margens do PDF, em milímetros. Obs.: a margem direita é sempre igual à
        //margem esquerda. A margem inferior *não* existe na FPDF, é definida aqui
        //apenas para controle se necessário ser maior do que a margem superior
        $margSup = $this->margem;
        $margEsq = $this->margem;
        $margInf = $this->margem;
        // posição inicial do conteúdo, a partir do canto superior esquerdo da página
        $xInic = $margEsq;
        $yInic = $margSup;
        $maxW = $this->paperwidth;
        $maxH = $tamPapelVert;
        //total inicial de paginas
        $totPag = 1;
        //largura imprimivel em mm: largura da folha menos as margens esq/direita
        $this->wPrint = $maxW - ($margEsq * 2);
        //comprimento (altura) imprimivel em mm: altura da folha menos as margens
        //superior e inferior
        $this->hPrint = $maxH - $margSup - $margInf;
        // estabelece contagem de paginas
        $this->pdf->aliasNbPages();
        $this->pdf->setMargins($margEsq, $margSup); // fixa as margens
        $this->pdf->setDrawColor(0, 0, 0);
        $this->pdf->setFillColor(255, 255, 255);
        $this->pdf->open(); // inicia o documento
        $this->pdf->addPage($this->orientacao, $this->papel); // adiciona a primeira página
        $this->pdf->setLineWidth(0.1); // define a largura da linha
        $this->pdf->setTextColor(0, 0, 0);

        $y = $this->blocoI(); //cabecalho
        $y = $this->blocoII($y); //informação cabeçalho fiscal e contingência

        $y = $this->blocoIII($y); //informação dos itens
        $y = $this->blocoIV($y); //informação sobre os totais
        $y = $this->blocoV($y); //informação sobre pagamento

        $y = $this->blocoVI($y); //informações sobre consulta pela chave
        $y = $this->blocoVII($y); //informações sobre o consumidor e dados da NFCe
        $y = $this->blocoIX($y); //informações complementares e sobre tributos
        try {
            $y = $this->blocoVIII($y); //QRCODE
        } catch (\Exception $e) {
        }
        $y = $this->blocoX($y); //creditos

        $ymark = $maxH / 4;
        if ($this->tpAmb == 2) {
            $this->pdf->setTextColor(120, 120, 120);
            $texto = "SEM VALOR FISCAL\nEmitida em ambiente de homologacao";
            $aFont = ['font' => $this->fontePadrao, 'size' => 14, 'style' => 'B'];
            $ymark += $this->pdf->textBox(
                $this->margem,
                $ymark,
                $this->wPrint,
                $maxH / 2,
                $texto,
                $aFont,
                'T',
                'C',
                false,
                '',
                false
            );
            $this->pdf->setTextColor(0, 0, 0);
        }
        if ($this->canceled) {
            $this->pdf->setTextColor(120, 120, 120);
            $texto = "CANCELADA";
            $aFont = ['font' => $this->fontePadrao, 'size' => 24, 'style' => 'B'];
            $this->pdf->textBox(
                $this->margem,
                $ymark + 4,
                $this->wPrint,
                $maxH / 2,
                $texto,
                $aFont,
                'T',
                'C',
                false,
                '',
                false
            );
            $aFont = ['font' => $this->fontePadrao, 'size' => 10, 'style' => 'B'];
            $this->pdf->textBox(
                $this->margem,
                $ymark + 14,
                $this->wPrint,
                $maxH / 2,
                $this->submessage,
                $aFont,
                'T',
                'C',
                false,
                '',
                false
            );
            $this->pdf->setTextColor(0, 0, 0);
        }

        if (!$this->canceled && $this->tpEmis == 9 && $this->offline_double) {
            $this->setViaEstabelecimento();
            //não está cancelada e foi emitida OFFLINE e está ativada a dupla impressão
            $this->pdf->addPage($this->orientacao, $this->papel); // adiciona a primeira página
            $this->pdf->setLineWidth(0.1); // define a largura da linha
            $this->pdf->setTextColor(0, 0, 0);
            $y = $this->blocoI(); //cabecalho
            $y = $this->blocoII($y); //informação cabeçalho fiscal e contingência
            $y = $this->blocoIII($y); //informação dos itens
            $y = $this->blocoIV($y); //informação sobre os totais
            $y = $this->blocoV($y); //informação sobre pagamento
            $y = $this->blocoVI($y); //informações sobre consulta pela chave
            $y = $this->blocoVII($y); //informações sobre o consumidor e dados da NFCe
            $y = $this->blocoVIII($y); //QRCODE
            $y = $this->blocoIX($y); //informações sobre tributos
            $y = $this->blocoX($y); //creditos
            $ymark = $maxH / 4;
            if ($this->tpAmb == 2) {
                $this->pdf->setTextColor(120, 120, 120);
                $texto = "SEM VALOR FISCAL\nEmitida em ambiente de homologacao";
                $aFont = ['font' => $this->fontePadrao, 'size' => 14, 'style' => 'B'];
                $ymark += $this->pdf->textBox(
                    $this->margem,
                    $ymark,
                    $this->wPrint,
                    $maxH / 2,
                    $texto,
                    $aFont,
                    'T',
                    'C',
                    false,
                    '',
                    false
                );
            }
            $this->pdf->setTextColor(0, 0, 0);
        }
    }


    private function calculatePaperLength()
    {
        $wprint = $this->paperwidth - (2 * $this->margem);
        $this->bloco3H = $this->calculateHeightItens($wprint * $this->descPercent);
        $this->bloco5H = $this->calculateHeightPag();
        $this->bloco9H = $this->calculateHeighBlokIX();

        $length = $this->bloco1H //cabecalho
            + $this->bloco2H //informação fiscal
            + $this->bloco3H //itens
            + $this->bloco4H //totais
            + $this->bloco5H //formas de pagamento
            + $this->bloco6H //informação do consumidor
            + $this->bloco7H //informações para consulta
            + $this->bloco8H //qrcode
            + $this->bloco9H //informações sobre tributos
            + $this->bloco10H; //informações do integrador
        return $length;
    }

    /**
     * Carrega os dados do xml na classe
     * @param string $xml
     *
     * @throws InvalidArgumentException
     */
    private function loadXml()
    {
        $this->dom = new Dom();
        $this->dom->loadXML($this->xml);
        $this->ide = $this->dom->getElementsByTagName("ide")->item(0);
        $mod = $this->getTagValue($this->ide, "mod");
        if ($this->getTagValue($this->ide, "mod") != '59') {
            throw new \Exception("O xml do Cupom Fiscal deve ser uma CF-e modelo 59");
        }
        $this->tpAmb = $this->getTagValue($this->ide, 'tpAmb');
        $this->nCFe = $this->getTagValue($this->ide, 'nCFe');
        $this->nfeProc = $this->dom->getElementsByTagName("nfeProc")->item(0) ?? null; //NAOACHEI
        $this->infProt = $this->dom->getElementsByTagName("infProt")->item(0) ?? null; //NAOACHEI
        $this->nfe = $this->dom->getElementsByTagName("cNF")->item(0);
        $this->infCFe = $this->dom->getElementsByTagName("infCFe")->item(0);
        $this->emit = $this->dom->getElementsByTagName("emit")->item(0);
        $this->enderEmit = $this->dom->getElementsByTagName("enderEmit")->item(0);
        $this->det = $this->dom->getElementsByTagName("det");
        $this->dest = $this->dom->getElementsByTagName("dest")->item(0);
        $this->enderDest = $this->dom->getElementsByTagName("enderDest")->item(0); //NAOACHEI
        $this->imposto = $this->dom->getElementsByTagName("imposto")->item(0);
        $this->total = $this->dom->getElementsByTagName("total")->item(0);
        $this->infAdic = $this->dom->getElementsByTagName("infAdic")->item(0);
        $this->tpEmis = $this->dom->getValue($this->ide, "tpEmis");
        $this->infCpl = '';
        if (!empty($this->infAdic)) {
            if (!empty($this->infAdic->getElementsByTagName("infCpl")->item(0))) {
                $this->infCpl = $this->infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue;
            }
        }
        $this->pag = $this->dom->getElementsByTagName("pgto");
        $this->qrCode = !empty($this->ide->getElementsByTagName('assinaturaQRCODE')->item(0)->nodeValue)
            ? $this->ide->getElementsByTagName('assinaturaQRCODE')->item(0)->nodeValue : null;
        $this->urlChave = !empty($this->dom->getElementsByTagName('urlChave')->item(0)->nodeValue)
            ? $this->dom->getElementsByTagName('urlChave')->item(0)->nodeValue : null;
        if (!empty($this->infProt)) {
            $cStat = $this->getTagValue($this->infProt, 'cStat');
            if (!in_array($cStat, [100, 150])) {
                $this->canceled = true;
            } elseif (!empty($retEvento = $this->nfeProc->getElementsByTagName('retEvento')->item(0))) {
                $infEvento = $retEvento->getElementsByTagName('infEvento')->item(0);
                $cStat = $this->getTagValue($infEvento, "cStat");
                $tpEvento = $this->getTagValue($infEvento, "tpEvento");
                $dhEvento = date(
                    "d/m/Y H:i:s",
                    $this->toTimestamp(
                        $this->getTagValue($infEvento, "dhRegEvento")
                    )
                );
                $nProt = $this->getTagValue($infEvento, "nProt");
                if (($tpEvento == '110111' || $tpEvento == '110112')
                    && ($cStat == '101'
                        || $cStat == '151'
                        || $cStat == '135'
                        || $cStat == '155')
                ) {
                    $this->canceled = true;
                    $this->submessage = "Data: {$dhEvento}\nProtocolo: {$nProt}";
                }
            }
        }
    }
}
