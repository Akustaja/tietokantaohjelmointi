<?php

function createDbConnection()
{
    $ini = parse_ini_file("config.ini");

    $host = $ini["host"];
    $db = $ini["database"];
    $username = $ini["user"];
    $pw = $ini["password"];

    try {
        $dbcon = new PDO("mysql:host=$host;dbname=$db", $username, $pw);
        return $dbcon;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    return null;
}