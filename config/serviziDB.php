<?php
//contiene i metodi per interrogare il DB

//recupera i dati della fattura ricercati per ID e li salva in un array associativo
function datiFattura($conn, $id) {
    $sql = 'SELECT * FROM FATTURE WHERE Id = :id';
    $stmt = $conn->prepare($sql);
     $stmt->bindParam(':id', $id);
    if (! $stmt->execute()) {
        throw new PDOException('Errore PDO ' . implode(',', $conn->errorInfo()));
    } else {
        $fattura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return  $fattura;

}

//recupera i dati della Net Surfing e li salva in un array associativo
function datiAzienda($conn){
    $sql = 'SELECT * FROM AZIENDA';
    $stmt = $conn->query($sql);
    $azienda = $stmt->fetchall(PDO::FETCH_ASSOC);
    return $azienda;
}


//recupera i dettagli dei pagamenti tramite idFattura e li salva in un array associatovo
function datiFatturePagamenti($conn,$idFattura){
    $sql = "SELECT * FROM FATTURE_PAGAMENTI WHERE idfattura = :id AND stato NOT LIKE" . "'ANNULLATO'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idFattura);
    if (! $stmt->execute()) {
        throw new PDOException('Errore PDO ' . implode(',', $conn->errorInfo()));
    } else {
        $totale = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $totale;
}

//recupera i dati del cliente tramite id e li salva in un arra associativo
function datiCliente($conn, $idCliente){
    $sql = "SELECT * FROM ANAGRAFICA_CLIENTI WHERE Id = :idCliente";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idCliente', $idCliente);
    $stmt->execute();
    if (! $stmt->execute()) {
        throw new PDOException('Errore PDO ' . implode(',', $conn->errorInfo()));
    } else {
        $cliente = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $cliente;
}


//recupera i dati della banca tramite Id e li salva in un array associativo
function datiBanca($conn, $idBanca){
    $sql = "SELECT * FROM BANCA WHERE IDb = :idBanca";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idBanca',$idBanca);
    $stmt->execute();
    if (! $stmt->execute()) {
        throw new PDOException('Errore PDO ' . implode(',', $conn->errorInfo()));
    } else {
        $banca = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $banca;
}

?>