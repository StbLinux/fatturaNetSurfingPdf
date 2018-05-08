<?php

    $language = 'en';

//intestazione tabella dei dati fattura
    $header = array('DESCRIPTION','PRICE','TAX','SUB-TOTALS');

    
//intestazione tabella dei totali
    $header2 = array ('GRAND TOTALS (Excl. Tax)' , 'TAX' , 'TAX Totals' , 'GRAND TOTALS');
    
//numero e data fatture
    $fattNum = "INVOICE N.";
    $fattData= "DATE";


//tipi di pagamento contenuti in un array
    $lbl_Pagamento = array ("Immediate payment","Direct remittance","cc. postale","Cash Order","Direct Debit");
    
    
    //etichette/testi della parte relativa alle condizione dei pagamenti
    $txtPagamento = "PAYMENT: ";
    $txtGgPag = "days EOM";
    $txtScadenza = "expiration date";
    $txtRate = "by installments:";
    $txtPagata = "=== PAID ===";
    $txtPvista = "payment on sight";
    $txtBanca = "BANK DETAILS";

//solo per le fatture estere inserisce la nazione nell'indirizzo di netsurfing    
    $naz="ITALY";
    
    
?>



