<?php

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database = $database", $user, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    throw new Exception("Error connecting to SQL Server" . $e->getMessage());
}

return $conn;


?>