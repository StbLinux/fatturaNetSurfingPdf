<?php

//variabili per la connesione a DB
 $serverName = "localhost";   
 $database = "webby";
 $user = "";
 $pwd = "";

//simbolo euro
    $euro = '€';
//valore attuale iva
    $ivaAttuale=22; 
//Setto i margini Superiore,Sinistro e Destro del Foglio
    $margineTop = 20;
    $margineLeft = 10;
    $margineRight = 10;
    
//larghezza e altezza del rettangolo dei dati del  cliente
    $larghezzaCliente=75;
    $altezzaCliente=40;
    
//posizione Y iniziale della tabella fattura    
    $posInizioTabFatt = 107; //i due valori devono essere uguali, questo serve per calcolare la distanza con la tabella sottostante
    $posYTabFatt  = 107; 

//posizione Y iniziale della tabella dei totali
    $posTabTot = 0;
    
    
//larghezza e altezza delle celle della tabella fattura
    $lgIntestazione = [130,25,10,25];
    $alCelle = 7;

//altezza riquadro bianco contenente le fatture 
    $alRiquadroTFatt = 100;
 
//contatore per verificare quante fatture sono state inserite nella tabella
    $numInserimenti = 1;    
    
//larghezza e altezza delle celle della tabella dei Totali
    $lgTotali = [55,20,55,60];
    $alCelle2 = 7;

//distanza fra la tabella dei totali e le informazioni sul pagamento / banca
    $alTestoPag = 10;
//distanza fra le condizioni di pagamento e i dati bancari
    $alTestoCC = 4;
    
//percorso dove verranno salvate le fatture
    $path = 'fatture/';
//percorso dove vengono spostate le fatture già presenti
    $path2 ='fattureOld/';
    

    
//funzione che converte la codifica del testo per poter visualizzare i caratteri speciali
    function convertiStriga($testo){
        $conversione = iconv('UTF-8', 'windows-1252', $testo);
        return $conversione;
    }
?>

