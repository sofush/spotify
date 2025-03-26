<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/logging/formatter.php';
require_once __DIR__ . '/entity/song.php';
require_once __DIR__ . '/entity/artist.php';
require_once __DIR__ . '/entity/album.php';
require_once __DIR__ . '/database.php';

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

function serve_static(string $filename)
{
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../static');
    $twig = new \Twig\Environment($loader);

    preg_match('/^[^\/]+\.(html|css|js|png|jpg|svg|ttf)$/', $filename, $ext);

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
        default => 'text/plaintext',
    };

    if ($filename === 'index.html') {
        global $em;
        $songs = $em->getRepository(Song::class)->findAll();
        $songsHtml = $twig->render('songs.html.twig', [
            'desc' => 'Likede sange',
            'songs' => $songs,
        ]);
        $albums = $em->getRepository(Album::class)->findAll();
        $albumsHtml = $twig->render('albums.html.twig', [
            'albums' => $albums,
        ]);
        $context = [
            'songs' => $songsHtml,
            'albums' => $albumsHtml
        ];
    }

    $body = $mime === 'text/html'
        ? $twig->render("$filename.twig", $context ?? [])
        : file_get_contents(__DIR__ . "/../static/$filename");

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
    }
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

$server->on('error', function (Throwable $e) use ($log) {
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
