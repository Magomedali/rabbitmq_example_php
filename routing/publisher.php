<?php

require __DIR__."/../vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$connection = new AMQPStreamConnection("localhost",5672,"guest","guest");

$channel = $connection->channel();

$exchange = "direct_logs";
$channel->exchange_declare($exchange,"direct",false,false,false);


$severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : "info";

$data = implode(" ", array_slice($argv, 2));

$data = empty($data) ? "Hello world" : $data;

$msg = new AMQPMessage($data);

$channel->basic_publish($msg,$exchange,$severity);

echo ' [x] Sent ', $severity, ':', $data, "\n";

$channel->close();
$connection->close();

?>