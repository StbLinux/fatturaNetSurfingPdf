<?php

    $language = 'ita';

//intestazione tabella dei dati fattura
    $header = array ('DESCRIZIONE','IMPONIBILE','IVA','TOTALE');
    
//intestazione tabella dei totali
    $header2 = array ('IMPONIBILE' , 'IVA' , 'IMPOSTA' , 'TOTALE');


//numero e data fatture
    $fattNum = "FATTURA N.";
    $fattData= "DATA";
    
    

//tipi di pagamento contenuti in un array
    $lbl_Pagamento = array ("Pagamento immediato","Rimessa diretta","cc. postale","RI.BA.", "RID");

    
//etichette/testi della parte relativa alle condizione dei pagamenti
    $txtPagamento = "PAGAMENTO: ";
    $txtGgPag = "gg. d.f. f.m.";
    $txtScadenza = "Scadenza";
    $txtRate = "con rate:";
    $txtPagata = "=== PAGATA ===";
    $txtPvista = "Pagamento a vista";
    $txtBanca = "BANCA";

//solo per le fatture estere inserisce la nazione nell'indirizzo di netsurfing    
    $naz="";

?>

