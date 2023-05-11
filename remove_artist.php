<?php
require_once 'dbconnection.php';
require_once 'headers.php';

$db = createDbConnection();

if (!isset($_GET["artist_id"])) {
    echo "Artist not defined!";
    exit;
}

try {
    $db->beginTransaction();
    $artist_id = $_GET["artist_id"];

    $query = $db->prepare("DELETE FROM invoice_items WHERE invoice_items.TrackId = ANY (
        SELECT TrackId FROM tracks WHERE tracks.AlbumId = ANY (
            SELECT DISTINCT AlbumId FROM albums, artists WHERE albums.ArtistId = :artist_id
            ));

            DELETE FROM playlist_track WHERE playlist_track.TrackId = ANY (
        SELECT TrackId FROM tracks WHERE tracks.AlbumId = ANY (
            SELECT DISTINCT AlbumId FROM albums, artists WHERE albums.ArtistId = :artist_id
            ));
        
            DELETE FROM tracks WHERE tracks.AlbumId = ANY (
            SELECT DISTINCT AlbumId FROM albums, artists WHERE albums.ArtistId = :artist_id
            );

            DELETE FROM albums WHERE albums.ArtistId = :artist_id;

            DELETE FROM artists WHERE ArtistId = :artist_id;
    ");
    $query->bindValue(":artist_id", $artist_id, PDO::PARAM_INT);
    $query->execute();
    $query->nextRowset();
    $query->nextRowset();
    $query->nextRowset();
    $query->nextRowset();
    
    

    $db->commit();

    header("HTTP/1.1 200 ok");
} catch (PDOException $pdoex) {
    $db->rollBack();
    header('HTTP/1.1 500 Internal Server Error');
    $error = array('error' => $pdoex->getMessage());
    echo json_encode($error);
    exit;
}
