<?php

require_once __DIR__."/../vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class FibonacciRpcClient{
	private $connection;
	private $channel;
	private $callback_queue;
	private $response;
	private $correlation_id;


	public function __construct(){
		$this->connection = new AMQPStreamConnection(
            'localhost',
            5672,
            'guest',
            'guest'
        );

        $this->channel = $this->connection->channel();

        list($this->callback_queue,,) = $this->channel->queue_declare("",false,false,true,false);

        $this->channel->basic_consume($this->callback_queue,'',false,false,false,false,[$this,'onResponse']);
	}


	public function onResponse($response){
		if($response->get("correlation_id") == $this->correlation_id){
			$this->response = $response->body;
		}
	}

	public function call($n){
		$this->response = null;
		$this->correlation_id = uniqid();

		$msg = new AMQPMessage((string)$n,[
			'correlation_id'=>$this->correlation_id,
			'reply_to'=>$this->callback_queue
		]);


		$this->channel->basic_publish($msg,'', 'rpc_queue');

		while(!$this->response){
			$this->channel->wait();
		}

		return intval($this->response);
	}
}

$n = isset($argv[1]) && (int)$argv[1] >= 0 ? (int)$argv[1] : 1;
echo "[.] Calc for ",$n,"\n";

$rpc = new FibonacciRpcClient();
$number = $rpc->call($n);
echo "[.] Got ",$number,"\n";
?>