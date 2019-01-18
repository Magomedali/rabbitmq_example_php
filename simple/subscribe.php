<?php

require __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

set_time_limit(0);

class SimpleReceiver{

	public function listen(){
		$connection = new AMQPConnection(
			"localhost",
			5672,
			"guest",
			"guest"
		);

		$channel = $connection->channel();

		$channel->queue_declare(
			"pizzaTime",
			false,
			true,
			false,
			false
		);

		$channel->basic_consume(
			"pizzaTime",
			'',
			false,
			true,
			false,
			false,
			array($this,"f_callback")
		);


		while(count($channel->callbacks)){
			$channel->wait();
		}

		$channel->close();
		$connection->close();
	}

	public function f_callback($msg){
		$str = $msg->body;
		// $f = fopen("orders.txt", "a+");
		// fwrite($f, "New message: ".$str."\n");
		// fclose($f);

		echo "New message: ".$str."\n";
	}
}


$s = new SimpleReceiver();
$s->listen();
?>