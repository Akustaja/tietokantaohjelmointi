<?php
require_once 'dbconnection.php';
require_once 'headers.php';


$db = createDbConnection();

if (!isset($_GET["artist_id"])) {
    echo "Artist not defined!";
    exit;
}

try {
    $artist_id = $_GET["artist_id"];

    $db->beginTransaction();

    $artist = $db->prepare("SELECT Name FROM artists WHERE ArtistId = $artist_id");
    $artist->execute();
    $albums = $db->prepare("SELECT Title, AlbumId FROM albums WHERE ArtistId = $artist_id");
    $albums->execute();
    $tracks = $db->prepare("SELECT Name, AlbumId FROM tracks WHERE tracks.AlbumId = ANY (
        SELECT DISTINCT AlbumId FROM albums, artists WHERE albums.ArtistId = $artist_id
                )");
    $tracks->execute();
    
    $db->commit();
    
    header("HTTP/1.1 200 ok");
    $dataArtist = $artist->fetch(PDO::FETCH_COLUMN, 1);
    $allAlbums = $albums->fetchAll(PDO::FETCH_COLUMN);
    $allTracks = $tracks->fetchAll(PDO::FETCH_COLUMN);

    $data = array("artist" => $dataArtist, "productions" => array("albums" => $allAlbums, "tracks" => array($allTracks))); //Tuntui, että pääsin lähelle ratkaisua mutta puhti loppui yrittäessä erotella kappaleet albumin mukaan.
    header('Content-Type: application/json');
    print json_encode($data);

} catch (PDOException $pdoex) {
    $db->rollBack();
    header('HTTP/1.1 500 Internal Server Error');
    $error = array('error' => $pdoex->getMessage());
    echo json_encode($error);
    exit;
}
