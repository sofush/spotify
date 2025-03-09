<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';
require_once __DIR__ . '/formatter.php';

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

$server = new HttpServer(function (ServerRequestInterface $request) {
    global $log;
    $log->info('test', ['context' => '1']);

    return Response::plaintext(
        'Hello World!\n'
    );
});

$server->on('error', function (Throwable $e) {
    global $log;
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
    $log->error($msg);
});

$addr = '127.0.0.1:8080';
$socket = new SocketServer($addr);
$server->listen($socket);

$log->debug("Server running at http://$addr");
