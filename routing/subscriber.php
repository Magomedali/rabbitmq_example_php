<?php

require __DIR__."/../vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection("localhost",5672,"guest","guest");

$channel = $connection->channel();

$exchange = "direct_logs";

$channel->exchange_declare($exchange,"direct",false,false,false);

list($queue_name, ,) = $channel->queue_declare("",false,false,true,false);

$severity = array_slice($argv, 1);

if(empty($severity)){
	file_put_contents("php://stderr", "Usage: $argv[0] [info] [warning] or [error]");
	exit(1);
}

foreach ($severity as $key => $s) {
	$channel->queue_bind($queue_name,$exchange,$s);
}

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function($msg){
	echo " [x] ", $msg->delivery_info['routing_key']," : ", $msg->body,"\n";
};

$channel->basic_consume($queue_name,'',false,true,false,false,$callback);

while (count($channel->callbacks)) {
	$channel->wait();
}

$channel->close();
$connection->close();
?>