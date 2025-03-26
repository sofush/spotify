<?php

require_once 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/entity'],
    isDevMode: true,
);
$connectionParams = [
    'driver' => 'pdo_mysql',
    'dbname' => 'spotify',
    'user' => 'root',
    'password' => 'root',
    'host' => '127.0.0.1',
    'port' => 3306,
];
$conn = DriverManager::getConnection($connectionParams);
$em = new EntityManager($conn, $config);
