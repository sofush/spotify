<?php

require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/entity/song.php';
require_once __DIR__ . '/../src/entity/artist.php';
require_once __DIR__ . '/../src/entity/album.php';

$conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
$conn->executeStatement('TRUNCATE TABLE albums_songs;');
$conn->executeStatement('TRUNCATE TABLE albums;');
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

$motherboard = new Song('Motherboard', '02:51', 'motherboard.jpg', 'Harmony.ogx', [$daftPunk]);
$loseYourself = new Song('Lose Yourself to Dance', '05:53', 'motherboard.jpg', null, [$daftPunk, $pharell]);
$contact = new Song('Contact', '06:23', 'motherboard.jpg', null, [$daftPunk]);
$songs = [$motherboard, $loseYourself, $contact];

foreach ($songs as $song) {
    $em->persist($song);
}

$randomAccess = new Album('Random Access Memories', 'motherboard.jpg', $daftPunk, $songs);
$em->persist($randomAccess);

$other = new Album('Discovery', 'discovery.png', $daftPunk, []);
$em->persist($other);

$em->flush();
