<?php

require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/entity/song.php';
require_once __DIR__ . '/../src/entity/artist.php';

$conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
$conn->executeStatement('TRUNCATE TABLE songs_artists;');
$conn->executeStatement('TRUNCATE TABLE artists;');
$conn->executeStatement('TRUNCATE TABLE songs;');
$conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

$artist = new Artist('Daft Punk', []);
$song = new Song('Motherboard', [$artist]);
$song = new Song('Lose Yourself to Dance', [$artist]);
$em->persist($artist);
$em->persist($song);
$em->flush();
