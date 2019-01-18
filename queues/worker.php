<?php

require __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;

set_time_limit(0);

class SimpleReceiver{

	public function listen(){
		$connection = new AMQPConnection("localhost",5672,"guest","guest");

		$channel = $connection->channel();

		$channel->queue_declare(
			"queues",
			false,
			true,
			false,
			false
		);

		//Вешаем на обработчик одновременно только одну очередь. 
		//Следующую очередь обработчик получит только после обработки предыдущей
		$channel->basic_qos(null, 1, null);

		$channel->basic_consume(
			"queues",
			'',
			false,
			false, //$no_ack = false  - оповещать об успешной обработке запросов 
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
		echo ' [x] Received ', $str, "\n";
		sleep(substr_count($str, '.'));
		echo " [x] Done\n";

		//Send ack to broker, if a parameter $no_ack is false 
		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
	}
}


$s = new SimpleReceiver();
$s->listen();
?>