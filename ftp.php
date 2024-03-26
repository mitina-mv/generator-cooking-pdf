<?php

// Подключаем автозагрузчик Composer
require 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(".env.local");

$ftp = new \FtpClient\FtpClient();
$ftp->connect($_ENV['FTP_HOST']);
$ftp->login($_ENV['FTP_USER'], $_ENV['FTP_PASSWORD']);

// загрузка всей папки
// $ftp->putAll('jsons', 'jsons');

$ftp->putFromPath(__DIR__.'/jsons/random.json');