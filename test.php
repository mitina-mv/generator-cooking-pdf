<?php

require_once ('vendor/autoload.php');
use \Dejurin\GoogleTranslateForFree;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// http://otisnth.ru:15672/#/
$connection = new AMQPStreamConnection('otisnth.ru', 5672, 'test', 'test');
$channel = $connection->channel();

$channel->queue_declare('hello', false, false, false, false);

$source = 'en';
$target = 'ru';
$attempts = 5;
$text = 'hello world';

$tr = new GoogleTranslateForFree();
$result = $tr->translate($source, $target, $text, $attempts);


$msg = new AMQPMessage($result);
$channel->basic_publish($msg, '', 'hello');

echo " [x] Sent '{$result}'\n";