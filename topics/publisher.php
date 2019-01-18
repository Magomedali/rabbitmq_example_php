<?php

require __DIR__."/../vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamconnection("localhost",5672,'guest','guest');

$channel = $connection->channel();

$exchange = "topics_excahnge";

$channel->exchange_declare($exchange,"topic",false,false,false);

$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : "anonymous.info";

$data = implode(" ",array_slice($argv, 2));

if(empty($data)){
	$data = "Hello world!";
}

$msg = new AMQPMessage($data);

$channel->basic_publish($msg,$exchange,$routing_key);

echo " [x] Sent ", $routing_key, ":", $data, "\n";

$channel->close();
$connection->close();
?>