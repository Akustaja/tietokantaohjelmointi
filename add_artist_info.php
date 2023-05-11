<?php
require_once 'dbconnection.php';
require_once 'headers.php';

$db = createDbConnection();
$body = file_get_contents("tracks.json");
$tracks = json_decode($body, true);

if (!isset($_POST["artist_name"]) || !isset($_POST["album_title"])) {
    echo "Parameters not defined!";
    exit;
}

try {
    $db->beginTransaction();

    $artist_name = htmlspecialchars($_POST["artist_name"]);
    $album_title = htmlspecialchars($_POST["album_title"]);

    $addArtist = $db->prepare("INSERT INTO artists (Name) VALUES ('$artist_name')");
    $addArtist->execute();
    $addAlbum = $db->prepare("INSERT INTO albums (Title, ArtistId) VALUES ('$album_title', (SELECT ArtistId FROM artists WHERE Name='$artist_name'))");
    $addAlbum->execute();
    $addTrack = $db->prepare("INSERT INTO tracks (Name, AlbumId, MediaTypeId, Composer, Milliseconds, UnitPrice) VALUES ('$tracks[name]', 
        (SELECT AlbumId FROM albums WHERE albums.ArtistId = (
            SELECT ArtistId FROM artists WHERE Name='$artist_name'
        )),
        '$tracks[type]',
        (SELECT Name FROM artists WHERE Name='$artist_name'),
        '$tracks[ms]',
        '$tracks[price]')");
    $addTrack->execute();
    
    

    $db->commit();

    header("HTTP/1.1 200 ok");
} catch (PDOException $pdoex) {
    $db->rollBack();
    header('HTTP/1.1 500 Internal Server Error');
    $error = array('error' => $pdoex->getMessage());
    echo json_encode($error);
    exit;
}
