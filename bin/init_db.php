<?php

require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/entity/song.php';
require_once __DIR__ . '/../src/entity/artist.php';

$conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
$conn->executeStatement('TRUNCATE TABLE songs_artists;');
$conn->executeStatement('TRUNCATE TABLE artists;');
$conn->executeStatement('TRUNCATE TABLE songs;');
$conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

$daftPunk = new Artist('Daft Punk', []);
$pharell = new Artist('Pharell Williams', []);
$artists = [$daftPunk, $pharell];

foreach ($artists as $artist) {
    $em->persist($artist);
}

$motherboard = new Song('Motherboard', '02:51', [$daftPunk]);
$loseYourself = new Song('Lose Yourself to Dance', '05:53', [$daftPunk, $pharell]);
$songs = [$motherboard, $loseYourself];

foreach ($songs as $song) {
    $em->persist($song);
}

$em->flush();
