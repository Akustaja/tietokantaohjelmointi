<?php
require_once 'dbconnection.php';
require_once 'headers.php';

$db = createDbConnection();

if(!isset($_GET["playlist_id"])) {
    echo "Playlist not defined!";
    exit;
}

try {
    $sql = "SELECT DISTINCT Name, Composer FROM playlist_track, tracks WHERE tracks.TrackId = ANY (
        SELECT TrackId FROM playlist_track WHERE playlist_track.PlaylistId = :playlist_id )";

    $query = $db->prepare($sql);
    $playlist_id = strip_tags($_GET["playlist_id"]);
    $query->bindParam(":playlist_id", $playlist_id, PDO::PARAM_INT);
    $query->execute();

    header("HTTP/1.1 200 ok");
    $allRows = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach($allRows as $row) {
        echo "<br>"."<h3>".$row["Name"]."</h3>"."<br> (".$row["Composer"].")";
    }
} catch (PDOException $pdoex) {
    header('HTTP/1.1 500 Internal Server Error');
    $error = array('error' => $pdoex->getMessage());
    echo json_encode($error);
    exit;
}