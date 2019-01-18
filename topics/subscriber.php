<?php

require __DIR__."/../vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamconnection;

$connection = new AMQPStreamconnection("localhost",5672,'guest','guest');

$channel = $connection->channel(); 

$exchange = "topics_excahnge";

$channel->exchange_declare($exchange,"topic",false,false,false);

list($queue_name, ,) = $channel->queue_declare("",false,false,true,false);

$binding_keys = array_slice($argv, 1);

if(empty($binding_keys)){
	file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
    exit(1);
}


foreach ($binding_keys as $key) {
	$channel->queue_bind($queue_name,$exchange,$key);
}

echo " [*] Waiting for logs. To exit press CTRL+C\n";


$callback = function($msg){
	echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};

$channel->basic_consume($queue_name,'',false,true,false,false,$callback);


while (count($channel->callbacks)) {
	$channel->wait();
}

$channel->close();
$connection->close();
?>