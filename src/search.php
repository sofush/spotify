<?php

use \Fuse\Fuse;

function search_songs($query)
{
    global $em;

    $songs = $em->getRepository(Song::class)->findAll();

    if ($query === '') {
        return array_slice($songs, 0, 5);
    }

    $titles = array_map(fn($song) => $song->getTitle(), $songs);
    $fuse = new Fuse($titles, ['threshold' => 1.0, 'distance' => 1000]);
    $results = $fuse->search($query, ['limit' => 5]);
    return array_map(fn($res) => $songs[$res['refIndex']], $results);
}

function search_albums($query)
{
    global $em;

    $albums = $em->getRepository(Album::class)->findAll();

    if ($query === '') {
        return array_slice($albums, 0, 5);
    }

    $titles = array_map(fn($album) => $album->getTitle(), $albums);
    $fuse = new Fuse($titles, ['threshold' => 1.0, 'ignoreLocation' => true, 'findAllMatches' => true]);
    $results = $fuse->search($query, ['limit' => 5]);
    return array_map(fn($res) => $albums[$res['refIndex']], $results);
}