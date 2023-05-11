<?php
require_once 'dbconnection.php';
require_once 'headers.php';

$db = createDbConnection();

if(!isset($_GET["item_id"])) {
    echo "Invoice item not defined!";
    exit;
}

try {
    $db->beginTransaction();
    $invoice_item_id = strip_tags($_GET["item_id"]);

    $query = $db->prepare("DELETE FROM invoice_items WHERE InvoiceLineId=:invoice_item_id");
    $query->bindValue(":invoice_item_id", $invoice_item_id, PDO::PARAM_INT);
    $query->execute();

    $db->commit();
    
    header("HTTP/1.1 200 ok");
} catch (PDOException $pdoex) {
    $db->rollBack();
    header('HTTP/1.1 500 Internal Server Error');
    $error = array('error' => $pdoex->getMessage());
    echo json_encode($error);
    exit;
}
