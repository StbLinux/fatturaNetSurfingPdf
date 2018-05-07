

<?php

require('fpdf.php');
include 'config/costanti.php';
include 'config/serviziDB.php';

//function hex2dec
//returns an associative array (keys: R,G,B) from a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000") {
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R'] = $rouge;
    $tbl_couleur['G'] = $vert;
    $tbl_couleur['B'] = $bleu;
    return $tbl_couleur;
}

//conversion pixel -> millimeter in 72 dpi
function px2mm($px) {
    return $px * 25.4 / 72;
}

function txtentities($html) {
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}

////////////////////////////////////


class Pdf extends FPDF {

//variabili per utilizzare il parse in html
    protected $B;
    protected $I;
    protected $U;
    protected $HREF;
    protected $fontList;
    protected $issetfont;
    protected $issetcolor;
//footer privacy
    private $footer = "<p> Net Surfing garantisce l'assoluta riservatezza delle informazioni relative ai dati personali del cliente che in nessun caso saranno divulgati" . "<br>" . "in forma nominativa. Si invita a prendere visione dell'informativa sulla privacy di Net Surfing al seguente indirizzo:" . "<br> " . " http://www.netsurf.it/it/azienda/privacy </p>";

    function __construct($orientation = 'P', $unit = 'mm', $format = 'A4') {
        //Call parent constructor
        parent::__construct($orientation, $unit, $format);

        //Initialization
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';

        $this->tableborder = 0;
        $this->tdbegin = false;
        $this->tdwidth = 0;
        $this->tdheight = 0;
        $this->tdalign = "L";
        $this->tdbgcolor = false;

        $this->oldx = 0;
        $this->oldy = 0;

        $this->fontlist = array("arial", "times", "courier", "helvetica", "symbol");
        $this->issetfont = false;
        $this->issetcolor = false;
    }

// crea l'header della pagina 
    function Header() {

// Logo
        $this->Image('img/netsurfing.png', 11, 20, 70);
    }

    function datiAzienda($nome, $indirizzo, $cap, $luogo, $prov, $mail, $web, $Piva_az, $NStel, $NSfax, $naz) {
        $this->SetFont('Arial', 'B', 15);
        $this->ln(18);
//celle che contengono i dati della Netsurfing nell'intestazione      
        $this->Cell(120, 5, $nome, 0, 1);
        $this->ln(1);
        $this->SetFont('Arial', '', 10);
        $this->Cell(120, 5, $indirizzo, 0, 1);
//se la nazione non è italia scrivo anche la nazione Italia
        if ($naz == '') {
            $this->Cell(120, 5, $cap . ' ' . $luogo . ' (' . $prov . ')', 0, 1);
        } else {
            $this->Cell(120, 5, $cap . ' ' . $luogo . ' (' . $prov . ') ' . $naz, 0, 1);
        }

        $this->Cell(120, 5, 'P.I. ' . $Piva_az, 0, 1);
//scrivo il codice fiscale solo se la fattura è nazionale
        if ($naz == '') {
            $this->Cell(120, 5, 'C.F. ' . $Piva_az, 0, 1);
        }
        $this->ln(1);
        $this->Cell(120, 5, 'Tel ' . $NStel, 0, 1);
        $this->Cell(120, 5, 'Fax ' . $NSfax, 0, 1);
        $this->Cell(120, 5, 'E-mail ' . $mail, 0, 1);
        $this->Cell(120, 5, 'Web ' . $web, 0, 1);
    }

    //crea il footer della pagina
    function Footer() {
// Position at 1.5 cm from bottom
        $this->SetY(-35);
// Arial italic 8
        $this->SetFont('Arial', 'I', 8);



        $this->SetLeftMargin(17);
        $this->WriteHTML("<p> Net Surfing garantisce l'assoluta riservatezza delle informazioni relative ai dati personali del cliente che in nessun caso saranno divulgati" .
                "<br>" .
                "in forma nominativa. Si invita a prendere visione dell'informativa sulla privacy di Net Surfing al seguente indirizzo:" . "<br>" .
                "https://www.netsurf.it/it/azienda/privacy-policy </p>");
        // Stampa il numero di pagina corrente e totale
        $this->SetX(0);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 1, 'C');
    }

//popola la parte relativa i dati della fattura
    function FatturaNumeroData($numeroFattura, $dfa, $dfm, $dfg, $fattNum, $fattData) {
        $numeroFattura = convertiStriga($numeroFattura);
        $fattNum = convertiStriga($fattNum);
        $fattData = convertiStriga($fattData);
        $dfa = convertiStriga($dfa);
        $dfm = convertiStriga($dfm);
        $dfg = convertiStriga($dfg);
        $this->ln(5);
        $this->SetFont('Arial', '', 12);
        $this->Cell(25, 5, $fattNum, 0, 0);
        $this->SetX(($this->GetX()) + 3);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(18, 5, "$numeroFattura", 0, 1);
        $this->SetFont('Arial', '', 12);
        $this->ln(2);
        $this->Cell(15, 5, $fattData, 0, 0);
        $this->SetX(($this->GetX()) + 13);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(18, 5, $dfg . '/' . $dfm . '/' . $dfa, 0, 1);
    }

    //funzione che fa andare a capo un testo, dato il numero di caratteri a cui andare a capo, il testo
//la coordinata x serve per dare la spaziatura rispetto al bordo sinistro
//la coordinata y serve per dare una maggiore spaziatura rispetto al bordo superiore

    function aCapo($x, $testo, $numCar, $y) {
        $testo = convertiStriga($testo);
        $lunghezzaTesto = strlen($testo);
        if ($lunghezzaTesto < $numCar) {
            $this->Cell(0, 4, $testo, 0, 1);
        } else {
            $txt = str_split($testo, $numCar);

            $this->Cell(0, 4, $txt[0], 0, 1);
            $this->setX($x + 1);
            $this->Cell(0, 4, $txt[1], 0, 1);
        }
        $this->setY($this->GetY() + $y);
    }

//disegna il rettangolo dove sono presenti i dati del cliente e li valorizza
    function DatiCliente($larghezza, $altezza, $ragSoc1, $ragSoc2, $via, $numeroCivico, $CAP, $citta, $provincia, $partitaIVA, $codiceFiscale, $nazione) {
//calcolo le coordinate x/y dell'angolo superiore sinistro 
        $posx = $this->GetPageWidth() - $GLOBALS['margineRight'] - $larghezza;
        $tmpPosx = $posx;
        $posy = $GLOBALS['margineTop'] + 20;
        $this->RoundedRect($posx, $posy, $larghezza, $altezza, 2);
        $this->setY($posy + 2);
        $tmpPosx += 1;
        $this->setX($tmpPosx);
        $this->SetFont('Arial', 'B', 11);
        $tmpPosx = $posx;
//scrivo la ragione Sociale verificando se è presente una seconda ragione sociale
        $this->MultiCell($larghezza, 4, $ragSoc1, 0, 1);
        $this->setX($tmpPosx + 1);
        if ($ragSoc2 != '') {
            $this->MultiCell($larghezza, 4, $ragSoc2, 0, 1);
        }
        $this->setY($this->GetY() + 4);
//scrivo la via ed il numero civico       
        $tmpPosx += 1;
        $this->setX($tmpPosx);
        $this->SetFont('Arial', '', 11);
        $this->MultiCell($larghezza, 4, $via . ', ' . $numeroCivico, 0, 1);
        $tmpPosx = $posx;
//scrivo Cap Citta Provincia e verifico se il cliente è estero
        $tmpPosx += 1;
        $this->setX($tmpPosx);
//se il cliente è fuori dall'italia scrivo la nazione       
        if ($nazione != 'ITALIA' && strlen($nazione) > 1) {
            $this->setX($tmpPosx);
            $this->MultiCell($larghezza, 4, $CAP . ' ' . $citta . ' (' . $provincia . ')' . '  ' . $nazione, 0, 1);
        } else {
            $this->MultiCell($larghezza, 4, $CAP . ' ' . $citta . ' (' . $provincia . ')', 0, 1);
        }

        $tmpPosx = $posx;
        $this->setY($this->GetY() + 4);
//verifica la partita iva e il codice fiscale e le scrive
        $tmpPosx += 1;
        $this->setX($tmpPosx);
//verifico se nella partita iva è presente il codice fiscale       
        if (strlen($partitaIVA) > 2 && $partitaIVA != '00000000000') {
            if (strlen($partitaIVA) > 15) {
                $this->aCapo($tmpPosx, "C.F.: " . $partitaIVA, 36, 1);
            } else {
                $this->aCapo($tmpPosx, "P.I.: " . $partitaIVA, 36, 1);
            }
        }
        $this->setX($tmpPosx);
        if (strlen($codiceFiscale) > 2 && $codiceFiscale != $partitaIVA) {
            $this->aCapo($tmpPosx, "C.F.: " . $codiceFiscale, 36, 1);
        }
    }

//disegna la tabella continete i dati della fattura
//riceve come parametri,l'altezza delle celle dell'intestazione, un array con le misure delle larghezze delle celle 
//un array con il testo delle  intestazioni delle colonne e l'altezza del riquadro che conterrà i dati delle fatture    
    function TabellaFattura($altezza, $larghezza, $intestazione, $alRiquadroTFatt) {
        $this->SetFont('Arial', '', 11);
//intestazione tabella fattura
        $this->SetFillColor(210, 210, 210);
        $this->SetTextColor(0, 0, 0);
        for ($i = 0; $i < count($intestazione); $i++) {
            $this->cell($larghezza[$i], $altezza, $intestazione[$i], 1, 0, 'C', true);
        }
        $this->SetY($this->GetY() + $altezza);
//contenitore tabella
        $this->SetFillColor(255, 255, 255);
        for ($i = 0; $i < count($intestazione); $i++) {
            $this->cell($larghezza[$i], $alRiquadroTFatt, '', 1, 0, 'C', true);
        }
    }

    function InserisciDatiFattura($posYTab, $alCelle, $margineLeft, $margineRight, $descrizione, $imponibile, $iva) {
//converto le stringhe per evitare problemi di visualizzazione con i caratteri speciali con i cartteri speciali
        $descrizione = convertiStriga($descrizione);
        $imponibile = convertiStriga($imponibile);
        $iva = convertiStriga($iva);
        $totale = ($imponibile + ($imponibile * $iva) / 100);
        $totale = sprintf("%01.2f", round($totale, 2));
        $imponibile = number_format($imponibile, 2, ',', '.');
        $totale = number_format($totale, 2, ',', '.');
        $euro = convertiStriga($GLOBALS['euro']);
// setto il font
        $this->SetFont('Arial', '', 10);
//memorizzo i dati globali della grandezza delle celle
//per inserirli nei calcoli delle coordinate X/Y, in maniera che per eventuali ridimensionamenti il testo rimanga sempre allineato    
        $larghezzaCellaDes = $GLOBALS['lgIntestazione'][0];
        $larghezzaCellaImp = $GLOBALS['lgIntestazione'][1];
        $larghezzaCellaIva = $GLOBALS['lgIntestazione'][2];
        $larghezzaCellaTot = $GLOBALS['lgIntestazione'][3];
//imposto la posizione di scrittura della prima riga della fattura        
        $posTestoY = $GLOBALS['posYTabFatt'] + 1;
//verifico se è già stata scritta una riga, in maniera da spostare la posizione di scrittura Y una riga sotto
        if ($GLOBALS['numInserimenti'] > 1) {
            $GLOBALS['yAttuale'] += 10;
            $GLOBALS['posYTabFatt'] = $GLOBALS['posYTabFatt'] + 12.5;
            $posTestoY = $GLOBALS['posYTabFatt'];
        }

//scrivo la descrizione calcolando le coordinate Y ed X
        $GLOBALS['yAttuale'] = $posTestoY + $alCelle + 1;
        $this->SetY($GLOBALS['yAttuale']);
        $this->SetX($margineLeft + 1);
        $this->MultiCell($larghezzaCellaDes, 5, $descrizione, 0, 'L');
//scrivo l'imponibile calcolando le coordinate Y ed X        
        $this->SetY($posTestoY + $alCelle);
        $this->SetX($larghezzaCellaDes + ($margineLeft - $larghezzaCellaImp + 4));
        $this->Cell($larghezzaCellaImp, $alCelle, $euro, 0, 0, 'R');
        $this->SetX($larghezzaCellaDes + ($margineLeft - 1));
        $this->Cell($larghezzaCellaImp, $alCelle, $imponibile, 0, 0, 'R');
//scrivo l'iva calcolando la coordinata X 
        $this->SetX($this->GetX() + 1);
        $this->Cell($larghezzaCellaIva, $alCelle, $iva . '%', 0, 0, 'C');
//scrivo il totale calcolando la coordinata X 
        $this->setX($this->GetPageWidth() - $margineRight - $larghezzaCellaTot - $margineLeft - $larghezzaCellaIva - 1);
        $this->Cell($larghezzaCellaTot, $alCelle, $euro, 0, 0, 'R');
        $this->setX($this->GetPageWidth() - $margineRight - $larghezzaCellaTot - 1);
        $this->Cell($larghezzaCellaTot, $alCelle, $totale, 0, 0, 'R');
//aumento il contatore per dell'inserimento fatture       
        $GLOBALS['numInserimenti'] += 1;
    }

//metodo che crea la tabella dei totali e ne valorizza i campi    
    function TabellaTot($altezza, $larghezza, $intestazione, $totaleImponibile, $iva, $totaleImposta, $totaleFattura) {
        $this->SetFont('Arial', '', 11);
        $this->SetFillColor(210, 210, 210);
        $this->SetTextColor(0, 0, 0);

        $totaleImponibile = sprintf("%01.2f", round($totaleImponibile, 2));
        $totaleImposta = sprintf("%01.2f", round($totaleImposta, 2));
        $totaleFattura = sprintf("%01.2f", round($totaleFattura, 2));
        $totaleImponibile = number_format($totaleImponibile, 2, ',', '.');
        $totaleImposta = number_format($totaleImposta, 2, ',', '.');
        $totaleFattura = number_format($totaleFattura, 2, ',', '.');
        $euro = convertiStriga('€');

//crea la tabella dei totali fattura
        for ($i = 0; $i < count($intestazione); $i++) {
            if ($intestazione[$i] != 'TOTALE') {
                $this->cell($larghezza[$i], $altezza, $intestazione[$i], 1, 0, 'C', true);
            } else {
                $this->SetFont('Arial', 'B', 11);
                $this->cell($larghezza[$i], $altezza, $intestazione[$i], 1, 0, 'C', true);
            }
        }
        $this->SetFillColor(255, 255, 255);
        $this->SetY($this->GetY() + $altezza);
        $this->SetFont('Arial', '', 11);

        $this->cell($larghezza[0], $altezza, $euro . ' ' . $totaleImponibile, 1, 0, 'C', true);
        $this->cell($larghezza[1], $altezza, $iva . '%', 1, 0, 'C', true);
        $this->cell($larghezza[2], $altezza, $euro . ' ' . $totaleImposta, 1, 0, 'C', true);
        $this->SetFont('Arial', 'B', 11);
        $this->cell($larghezza[3], $altezza, $euro . ' ' . $totaleFattura, 1, 0, 'C', true);



        $this->SetY($this->GetY() + $altezza);
    }

    function DatiPagamento($txtCampoTemp, $txtCampoTemp1) {
        $xAttuale = 0 + $GLOBALS['margineLeft'];

        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 0, $txtCampoTemp, 0);
        $xAttuale += $this->GetStringWidth($txtCampoTemp);
        $this->SetX($xAttuale + 7);
        $this->Cell(0, 0, $txtCampoTemp1, 0);
        $xAttuale += $this->GetStringWidth($txtCampoTemp1);
        $this->SetY($this->GetY() + 5);
    }

    public function DatiPagamentoRate($descrizione, $importo, $giorno, $mese, $anno, $euro) {
        $euro = convertiStriga($euro);
        $descrizione = convertiStriga($descrizione);
        $importo = sprintf("%01.2f", round($importo, 2));
        $importo = number_format($importo, 2, ',', '.');
        $this->SetX(44);
        $this->Cell(0, 0, $descrizione . '  ' . $euro . '  ' . $importo . '  ' . $giorno . '/' . $mese . '/' . $anno, 0, 1);
        $this->SetY($this->GetY() + 5);
    }

    function WriteHTML($html) {
        $html = strip_tags($html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote><hr><td><tr><table><sup>"); //remove all unsupported tags
        $html = str_replace("\n", '', $html); //replace carriage returns with spaces
        $html = str_replace("\t", '', $html); //replace carriage returns with spaces
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //explode the string
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                //Text
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                elseif ($this->tdbegin) {
                    if (trim($e) != '' && $e != "&nbsp;") {
                        $this->Cell($this->tdwidth, $this->tdheight, $e, $this->tableborder, '', $this->tdalign, $this->tdbgcolor);
                    } elseif ($e == "&nbsp;") {
                        $this->Cell($this->tdwidth, $this->tdheight, '', $this->tableborder, '', $this->tdalign, $this->tdbgcolor);
                    }
                } else
                    $this->Write(5, stripslashes(txtentities($e)));
            }
            else {
                //Tag
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    //Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr) {
        //Opening tag
        switch ($tag) {

            case 'SUP':
                if (!empty($attr['SUP'])) {
                    //Set current font to 6pt     
                    $this->SetFont('', '', 6);
                    //Start 125cm plus width of cell to the right of left margin         
                    //Superscript "1" 
                    $this->Cell(2, 2, $attr['SUP'], 0, 0, 'L');
                }
                break;

            case 'TABLE': // TABLE-BEGIN
                if (!empty($attr['BORDER']))
                    $this->tableborder = $attr['BORDER'];
                else
                    $this->tableborder = 0;
                break;
            case 'TR': //TR-BEGIN
                break;
            case 'TD': // TD-BEGIN
                if (!empty($attr['WIDTH']))
                    $this->tdwidth = ($attr['WIDTH'] / 4);
                else
                    $this->tdwidth = 40; // Set to your own width if you need bigger fixed cells
                if (!empty($attr['HEIGHT']))
                    $this->tdheight = ($attr['HEIGHT'] / 6);
                else
                    $this->tdheight = 6; // Set to your own height if you need bigger fixed cells
                if (!empty($attr['ALIGN'])) {
                    $align = $attr['ALIGN'];
                    if ($align == 'LEFT')
                        $this->tdalign = 'L';
                    if ($align == 'CENTER')
                        $this->tdalign = 'C';
                    if ($align == 'RIGHT')
                        $this->tdalign = 'R';
                } else
                    $this->tdalign = 'L'; // Set to your own
                if (!empty($attr['BGCOLOR'])) {
                    $coul = hex2dec($attr['BGCOLOR']);
                    $this->SetFillColor($coul['R'], $coul['G'], $coul['B']);
                    $this->tdbgcolor = true;
                }
                $this->tdbegin = true;
                break;

            case 'HR':
                if (!empty($attr['WIDTH']))
                    $Width = $attr['WIDTH'];
                else
                    $Width = $this->w - $this->lMargin - $this->rMargin;
                $x = $this->GetX();
                $y = $this->GetY();
                $this->SetLineWidth(0.2);
                $this->Line($x, $y, $x + $Width, $y);
                $this->SetLineWidth(0.2);
                $this->Ln(1);
                break;
            case 'STRONG':
                $this->SetStyle('B', true);
                break;
            case 'EM':
                $this->SetStyle('I', true);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->SetStyle($tag, true);
                break;
            case 'A':
                $this->HREF = $attr['HREF'];
                break;
            case 'IMG':
                if (isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if (!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if (!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                }
                break;
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->Ln(10);
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR'] != '') {
                    $coul = hex2dec($attr['COLOR']);
                    $this->SetTextColor($coul['R'], $coul['G'], $coul['B']);
                    $this->issetcolor = true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont = true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist) && isset($attr['SIZE']) && $attr['SIZE'] != '') {
                    $this->SetFont(strtolower($attr['FACE']), '', $attr['SIZE']);
                    $this->issetfont = true;
                }
                break;
        }
    }

    function CloseTag($tag) {
        //Closing tag
        if ($tag == 'SUP') {
            
        }

        if ($tag == 'TD') { // TD-END
            $this->tdbegin = false;
            $this->tdwidth = 0;
            $this->tdheight = 0;
            $this->tdalign = "L";
            $this->tdbgcolor = false;
        }
        if ($tag == 'TR') { // TR-END
            $this->Ln();
        }
        if ($tag == 'TABLE') { // TABLE-END
            $this->tableborder = 0;
        }

        if ($tag == 'STRONG')
            $tag = 'B';
        if ($tag == 'EM')
            $tag = 'I';
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
        if ($tag == 'FONT') {
            if ($this->issetcolor == true) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont('arial');
                $this->issetfont = false;
            }
        }
    }

    function SetStyle($tag, $enable) {
        //Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0)
                $style .= $s;
        }
        $this->SetFont('', $style);
    }

    function PutLink($URL, $txt) {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1 * $this->k, ($h - $y1) * $this->k, $x2 * $this->k, ($h - $y2) * $this->k, $x3 * $this->k, ($h - $y3) * $this->k));
    }

}

$pdf = new PDF();

//crea la connessione con il database
try {
    $conn = new PDO("sqlsrv:server=$serverName;Database = $database", $user, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    throw new Exception("Error connecting to SQL Server" . $e->getMessage());
}

//richiama la funzione che restituisce un array contenente i dati della tabella fattura
//la fattura viene ricercata per ID   
$idFattura = $_REQUEST['idFattura']; //id FATTURE CHE ARRIVA VIA POST/GET
$fattura = datiFattura($conn, $idFattura);
if(count($fattura)<1){
    echo "Fattura non trovata";
    die;
}

//i dati vengono messi all'interno di variabili per comodità  
$nFattura = $fattura[0]["NFattura"];
$idCliente = $fattura[0]["IdCliente"];
$dataFattura = $fattura[0]["DataFattura"];
//della data prendo solo i dati realtivi alla data tagliando la parte delle ore
//e salvo anno mese giorno in 3 variabili
$dataFattura = substr($dataFattura, 0, 10);
list($dfa, $dfm, $dfg) = explode("-", $dataFattura);

$causale1 = $fattura[0]["Causale1"];
$commessa1 = $fattura[0]["Commessa1"];
$imponibile1 = $fattura[0]["Imponibile1"];
$imponibile1E = $fattura[0]["Imponibile1E"];

$causale2 = $fattura[0]["Causale2"];
$commessa2 = $fattura[0]["Commessa2"];
$imponibile2 = $fattura[0]["Imponibile2"];
$imponibile2E = $fattura[0]["Imponibile2E"];

$causale3 = $fattura[0]["Causale3"];
$commessa3 = $fattura[0]["Commessa3"];
$imponibile3 = $fattura[0]["Imponibile3"];
$imponibile3E = $fattura[0]["Imponibile3E"];

$pagamentoA = $fattura[0]["PagamentoA"];
$pagamentoB = $fattura[0]["PagamentoB"];

$iva = $fattura[0]["IVA"];
$totaleImponibile = $fattura[0]["TotaleImponibile"];
$totaleImponibileE = $fattura[0]["TotaleImponibileE"];
$totaleImposta = $fattura[0]["TotaleImposta"];
$totaleImpostaE = $fattura[0]["TotaleImpostaE"];
$totaleFattura = $fattura[0]["TotaleFattura"];
$totaleFatturaE = $fattura[0]["TotaleFatturaE"];

$RIBA = $fattura[0]["Riba"];

$scadeIl = $fattura[0]["ScadeIl"];
$scadeIl = substr($scadeIl, 0, 10);
list($aaaa, $mm, $gg) = explode("-", $scadeIl);

$pagataIl = $fattura[0]["PagataIl"];
$status = $fattura[0]["Status"];
$note = $fattura[0]["Note"];

//ottengo i dati della Netsurfing
$datiAzienda = datiAzienda($conn);

$nome = $datiAzienda[0]["nome"];
$indirizzo = $datiAzienda[0]["indirizzo"];
$cap = $datiAzienda[0]["cap"];
$luogo = $datiAzienda[0]["luogo"];
$prov = $datiAzienda[0]["prov"];
$mail = $datiAzienda[0]["mail"];
$web = $datiAzienda[0]["web"];
$logo = $datiAzienda[0]["logo"];
$Piva_az = $datiAzienda[0]["iva"];
$NStel = $datiAzienda[0]["tel"];
$NSfax = $datiAzienda[0]["fax"];






//array che conterranno gli imponibili e le causali estratte dal DB    
$causali = [];
$imponibili = [];

if ($causale1 != '' && $imponibile1 != '') {
    $causali[] = trim($causale1);
    $imponibili[] = sprintf("%01.2f", round($imponibile1, 2));
}
if ($causale2 != '' && $imponibile2 != '') {
    $causali[] = trim($causale2);
    $imponibili[] = sprintf("%01.2f", round($imponibile2, 2));
}
if ($causale3 != '' && $imponibile3 != '') {
    $causali[] = trim($causale3);
    $imponibili[] = sprintf("%01.2f", round($imponibile3, 2));
}


if ($imponibile1 == '') {
    $imponibile1 = 0;
}
if ($imponibile2 == '') {
    $imponibile2 = 0;
}
if ($imponibile3 == '') {
    $imponibile3 = 0;
}
if ($totaleFattura == ''){
    $totaleFattura = 0;
}
if ($totaleFattura == ''){
    $totaleFattura = 0;
}
if ($pagamentoA == ''){
    $pagamentoA = '2';
}
if ($pagamentoB == ''){
    $pagamentoB = '1';
}


//recupero i dati del cliente

$cliente = datiCliente($conn, $idCliente);

$ragioneSociale = $cliente[0]["RagioneSociale"];
$nazione = $cliente[0]["Stato"];
$estero = 0;
$via = $cliente[0]["Via"];
$numeroCivico = $cliente[0]["NumeroCivico"];
$citta = $cliente[0]["Citta"];
$provincia = $cliente[0]["Provincia"];
$CAP = $cliente[0]["CAP"];
$partitaIVA = $cliente[0]["PartitaIVA"];
$codiceFiscale = $cliente[0]["CodiceFiscale"];
$esenteIVA = $cliente[0]["EsenteIVA"];
$ragSoc1 = $cliente[0]["RagSoc1"];
$ragSoc2 = $cliente[0]["RagSoc2"];
$idBanca = $cliente[0]["IdBanca"];


if ($cliente[0]["Rating"] == 2) {
    include 'config/lang_eng.php';
} else {
    include 'config/lang_it.php';
}


//verifico l'eventuale esenzione dell'iva
if ($iva === "") {
    if ($esenteIVA === 1) {
        $iva = 0;
    } else {
        $iva = $ivaAttuale;
    }
}

$fileName = $nFattura .'_'.$idFattura .'.pdf';
$esito = "Fattura salvata correttamente";
//se la fattura è già presente nella cartella la copia in una cartella delle vecchie fatture,assegnandole un nome univoco.
if (file_exists($path . $fileName)) {
//genero un file name composto dalla data attuale comprensiva anche di ore minuti e secondi
    $fileNameNew = $nFattura . '_'.$idFattura.'_' . date("dmyHis") . '.pdf';
    copy($path . $fileName, $path2 . $fileNameNew);
    $esito = "File $fileName  già esistente, copiato il file nella directory $path2 con nome $fileNameNew";
}


//Inizia la creazione del pdf

$pdf->AliasNbPages();
//spessore delle linee
$pdf->SetLineWidth(0.4);
//setta i margini del foglio a4
$pdf->SetMargins($margineLeft, $margineTop, $margineRight);
//aggiunge una nuova pagina
$pdf->AddPage();
//metodo che inserisce i dati dell'azienda
$pdf->datiAzienda($nome, $indirizzo, $cap, $luogo, $prov, $mail, $web, $Piva_az, $NStel, $NSfax, $naz);
//metodo che inserisce il numero della fattura e la data
$pdf->FatturaNumeroData($nFattura, $dfa, $dfm, $dfg, $fattNum, $fattData);
//inserisce i dati del cliente nel riquadro in alto a destra
$pdf->DatiCliente($larghezzaCliente, $altezzaCliente, $ragSoc1, $ragSoc2, $via, $numeroCivico, $CAP, $citta, $provincia, $partitaIVA, $codiceFiscale, $nazione);

//metodo che disegna la tabella delle fatture
$pdf->SetY($posYTabFatt); //110 y
$pdf->TabellaFattura($alCelle, $lgIntestazione, $header, $alRiquadroTFatt);


//inserisco i dati della fattura nella tabella 
for ($i = 0; $i < count($causali); $i++) {
    $pdf->InserisciDatiFattura($posYTabFatt, $alCelle, $margineLeft, $margineRight, $causali[$i], $imponibili[$i], $iva);
}



//setto la posizione della tabella dei totali
$posYTabFatt = $posInizioTabFatt;

//imposto la y della posizione della tabella dei totali
$pdf->SetY($posYTabFatt + $alRiquadroTFatt + 15);

//disegno la tabella dei totali
$pdf->TabellaTot($alCelle2, $lgTotali, $header2, $totaleImponibile, $iva, $totaleImposta, $totaleFattura);

//imposto la y della posizione per la scrittura dei dati pagamento
$pdf->SetY($pdf->GetY() + $alTestoPag);

//dati pagamento/banca che arriveranno da DB
$txtCampoTemp = "";
$txtCampoTemp1 = "";


//'CONDIZIONI DI PAGAMENTO
if ($pagamentoA == 0 && $status == 'PAGATA') {
    $txtCampoTemp = $txtPagata;
    $pdf->DatiPagamento($txtCampoTemp, $txtCampoTemp1);
} else {
    $txtCampoTemp = $txtPagamento;

//Estraggo eventuali condizioni di pagamento
    $txtCampoTemp1 = $lbl_Pagamento[$pagamentoB];

//interrogo la tabella FATTURE_PAGAMENTI passando l'id della fattura
    $tot = datiFatturePagamenti($conn, $idFattura);
//se ottengo più rate 
    if (count($tot) > 1) {
        $txtCampoTemp1 = $txtCampoTemp1 . " " . $txtRate;
    } else {
        if ((($pagamentoA - 1) * 30) > 0) {
            $txtCampoTemp1 = $txtCampoTemp1 . " " . (($pagamentoA - 1) * 30);
            $txtCampoTemp1 = $txtCampoTemp1 . " " . $txtGgPag;
        }
        $txtCampoTemp1 = $txtCampoTemp1 . " - " . $txtScadenza . " " . $gg . "/" . $mm . "/" . $aaaa;
    }

//scrivo le condizioni di pagamento generate fino al momento
//se la fattura non ha rate o condizioni particolari le condizioni sono terminate

    if (count($tot) > 1) {
        $pdf->AddPage();
        $pdf->SetY($margineTop + 25);
        $pdf->DatiPagamento($txtCampoTemp, $txtCampoTemp1);
        foreach ($tot as $fatt) {
            $scad = $fatt['DataScadenza'];
            $scad = substr($scad, 0, 10);
            list($anno, $mese, $giorno) = explode("-", $scad);
            $pdf->DatiPagamentoRate($fatt['Descrizione'], $fatt['Importo'], $giorno, $mese, $anno, $euro);
        }
    } else {
        $pdf->DatiPagamento($txtCampoTemp, $txtCampoTemp1);
    }
}



//imposto la y della posizione per la scrittura dei bancari
$pdf->SetY($pdf->GetY() + $alTestoCC);

//estremi cc bancario

if ($pagamentoA != 0 && $pagamentoB == 0) {
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 0, $txtPvista, 0, 1);
}
//variabile che contiene i dati della banca acquisiti da database
$banca = '';
if ($pagamentoA != 0) {
    $banca = datiBanca($conn, $idBanca);

    // Se sul cliente non è associato alcun conto, estraggo quello di default
    if (count($banca < 1)) {
        $banca = datiBanca($conn, 39);
    }

    if ($language == 'ita') {
        $tBanca = $txtBanca . ' ' . $banca[0]['bank'] . ':';
        $tIban = ' IBAN ' . $banca[0]['iban'];
        if ($banca[0]['swift'] != '') {
            $tIban = $tIban . ' (SWIFT: ' . $banca[0]['swift'] . ')';
        }

        $pdf->Cell(0, 0, $tBanca . $tIban, 0, 1);
    } else {
        $tBanca = $txtBanca . ': ' . $banca[0]['bank'];
        $tIban = ' IBAN ' . $banca[0]['iban'];
        if ($banca[0]['swift'] != '') {
            $tIban = $tIban . ' (SWIFT: ' . $banca[0]['swift'] . ')';
        }
        $tBanca = convertiStriga($tBanca);
        $tIban = convertiStriga($tIban);
        $pdf->Cell(0, 0, $tBanca . $tIban, 0, 1);
    }
}


//salva la fattura su disco
//attualmente come prova la salva in una cartella inclusa nel progetto
$pdf->Output('F', $path . $fileName);
echo $esito;
//header("location: index.html");



?>
