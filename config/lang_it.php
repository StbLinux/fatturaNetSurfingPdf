<?php

    $language = 'ita';

//intestazione tabella dei dati fattura
    $header = ['DESCRIZIONE','IMPONIBILE','IVA','TOTALE'];
    
//intestazione tabella dei totali
    $header2 = ['IMPONIBILE' , 'IVA' , 'IMPOSTA' , 'TOTALE'];


//numero e data fatture
    $fattNum = "FATTURA N.";
    $fattData= "DATA";
    
    

//tipi di pagamento contenuti in un array
   
    $lbl_Pagamento[0] = "Pagamento immediato";
    $lbl_Pagamento[1] = "Rimessa diretta";
    $lbl_Pagamento[2] = "cc. postale";
    $lbl_Pagamento[3] = "RI.BA.";
    $lbl_Pagamento[4] = "RID" ;  
    
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

