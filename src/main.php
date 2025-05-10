<?php

declare(strict_types=1);
namespace Sofus\Spotify;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/logging/formatter.php';
require_once __DIR__ . '/entity/song.php';
require_once __DIR__ . '/entity/artist.php';
require_once __DIR__ . '/entity/album.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/search.php';

use Album;
use Color;
use ColorCode;
use Colorizer;
use ConsoleFormatter;
use Song;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use React\Http\HttpServer;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Socket\SocketServer;

$log = new Logger('spotify');
$stream = new StreamHandler('php://stdout');
$stream->setFormatter(new ConsoleFormatter());
$log->pushHandler($stream);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../static');
$twig = new \Twig\Environment($loader);

function get_front_context($twig)
{
    $songsHtml = $twig->render('songs.html.twig', get_songs_context());
    $albumsHtml = $twig->render('albums.html.twig', get_albums_context());
    return [
        'songsHtml' => $songsHtml,
        'albumsHtml' => $albumsHtml,
    ];
}

function get_player_context($id)
{
    global $em;
    $song = $em->getRepository(Song::class)->find($id);
    return ['song' => $song];
}

function get_album_context($id)
{
    global $twig;
    global $em;
    $album = $em->getRepository(Album::class)->find($id);
    $songsContext = [
        'desc' => 'Sange i albummet',
        'songs' => $album->getSongs(),
    ];
    $songsHtml = $twig->render('songs.html.twig', $songsContext);
    return [
        'album' => $album,
        'songsHtml' => $songsHtml,
    ];
}

function get_songs_context()
{
    global $em;
    $songs = $em->getRepository(Song::class)->findAll();
    return [
        'desc' => 'Sange',
        'songs' => $songs,
    ];
}

function get_albums_context()
{
    global $em;
    $albums = $em->getRepository(Album::class)->findAll();
    return [
        'albums' => $albums,
    ];
}

function get_search_context($query)
{
    global $twig;

    $songs = search_songs($query);
    $songsCtx = [
        'desc' => 'Sange',
        'songs' => $songs,
    ];
    $songsHtml = $twig->render('songs.html.twig', $songsCtx);

    $albums = search_albums($query);
    $albumsCtx = [
        'albums' => $albums,
    ];
    $albumsHtml = $twig->render('albums.html.twig', $albumsCtx);

    return [
        'songsHtml' => $songsHtml,
        'albumsHtml' => $albumsHtml,
    ];
}

function serve_static(string $filename, array $context = null)
{
    global $twig;
    preg_match('/^[^\/]+\.(html|css|js|png|jpg|svg|ttf|ogx)$/', $filename, $ext);

    if (count($ext) < 2) {
        return;
    }

    $mime = match ($ext[1]) {
        'css' => 'text/css',
        'js' => 'text/javascript',
        'html' => 'text/html',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'ttf' => 'font/ttf',
        'jpg' => 'image/jpeg',
        'ogx' => 'application/ogg',
        default => 'text/plaintext',
    };

    if ($mime === 'text/html') {
        $context ??= match ($filename) {
            'front.html' => get_front_context($twig),
            'songs.html' => get_songs_context(),
            'albums.html' => get_albums_context(),
            default => [],
        };

        $body = $twig->render("$filename.twig", $context);
    } else {
        $body = file_get_contents(__DIR__ . "/../static/$filename");
    }

    return new Response(
        200,
        ['Content-Type' => "$mime;charset=UTF-8"],
        $body,
    );
}

$middlewares = [
    function (ServerRequestInterface $request) use ($log) {
        $methodColor = Color::new()->code(ColorCode::Magenta);
        $uriColor = Color::new()->bold();
        $out = Colorizer::builder()
            ->push($request->getMethod(), $methodColor, ' ')
            ->push($request->getUri()->getPath(), $uriColor, ' ')
            ->build();

        $log->info($out);
    },
    function (ServerRequestInterface $request) {
        if (in_array($request->getUri()->getPath(), ['', '/'])) {
            return serve_static('index.html');
        }
    },
    function (ServerRequestInterface $request) {
        $path = $request->getUri()->getPath();

        if (preg_match('/^\/static\/([^\/]+)$/', $path, $matches)) {
            $filename = $matches[1];
            return serve_static($filename);
        }
    },
    function (ServerRequestInterface $request) {
        $path = $request->getUri()->getPath();

        if (preg_match('/^\/song\/(\d+)$/', $path, $matches)) {
            $num = $matches[1];
            return serve_static('player.html', get_player_context($num));
        }
    },
    function (ServerRequestInterface $request) {
        $path = $request->getUri()->getPath();

        if (preg_match('/^\/album\/(\d+)$/', $path, $matches)) {
            $num = $matches[1];
            return serve_static('album.html', get_album_context($num));
        }
    },
    function (ServerRequestInterface $request) {
        $path = $request->getUri()->getPath();

        if ($path === '/add-album') {
            return serve_static('add-album.html');
        }
    },
    function (ServerRequestInterface $request) {
        $path = $request->getUri()->getPath();

        if (preg_match('/^\/search$/', $path, $matches)) {
            $params = $request->getQueryParams();

            if (array_key_exists('q', $params)) {
                $query = $params['q'];
            }

            return serve_static('search.html', get_search_context($query ?? ''));
        }
    },
];

$server = new HttpServer(function (ServerRequestInterface $request) use ($middlewares) {
    foreach ($middlewares as $middleware) {
        $res = $middleware($request);

        if ($res instanceof Response) {
            return $res;
        }
    }

    return new Response(
        Response::STATUS_NOT_FOUND,
        ['Content-Type' => 'text/html'],
        '<h1>404 not found</h1>'
    );
});

$server->on('error', function ($e) use ($log) {
    $file = mb_strtolower($e->getFile());
    $dir = mb_strtolower(getcwd());

    if (str_starts_with($file, $dir)) {
        $file = '.' . DIRECTORY_SEPARATOR . mb_substr($file, mb_strlen($dir) + 1);
    }

    $msg = sprintf(
        'Error in %s line %s: %s',
        $file,
        $e->getline(),
        $e->getMessage(),
    );
    $msg = Colorizer::builder()
        ->push($msg, null, PHP_EOL)
        ->push(strval($e), Color::new(ColorCode::BrightRed))
        ->build();
    $log->error($msg);
});

$fd = getenv('LISTEN_FDS_FIRST_FD');
$port = getenv('PORT');

if ($fd !== false) {
    $log->debug('Starting TCP server backed by file descriptor...');
    $addr = "php://fd/$fd";
} else if ($port !== false) {
    $log->debug("Starting TCP server with port $port...");
    $addr = "127.0.0.1:$port";
} else {
    $log->debug("Starting TCP server with random port...");
    $addr = '127.0.0.1:0';
}

$socket = new SocketServer($addr);
$server->listen($socket);

$log->debug('Server running at ' . $socket->getAddress());
