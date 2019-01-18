<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;


$sended = false;
if(isset($_POST['task'])){
	
	$data = filter_input(INPUT_POST, "task", FILTER_SANITIZE_STRING);
	$message = $data;

	$connection = new AMQPConnection("localhost",5672,'guest','guest');

	$channel = $connection->channel();

	$channel->queue_declare(
        'queues',    #queue name - Имя очереди может содержать до 255 байт UTF-8 символов
        false,          #passive - может использоваться для проверки того, инициирован ли обмен, без того, чтобы изменять состояние сервера
        true,          #durable - убедимся, что RabbitMQ никогда не потеряет очередь при падении - очередь переживёт перезагрузку брокера
        false,          #exclusive - используется только одним соединением, и очередь будет удалена при закрытии соединения
        false           #autodelete - очередь удаляется, когда отписывается последний подписчик
    );

	// AMQPMessage::DELIVERY_MODE_PERSISTENT = 2
	$msg = new AMQPMessage($message,['delivery_mode'=>2]);

	$channel->basic_publish(
            $msg,           #message
            '',             #exchange
            'queues'     #routing key
    );

	$channel->close();
	$connection->close();

	$sended = true;
}

?>

<div>
	<h3>Create new task</h3>
	<form action="" method="POST">
		<textarea name="task" required>
			<?php 
				if($sended){
					echo $message;
				}
			?>
		</textarea>
		<input type="submit" value="GET">
	</form>
</div>

<?php 
	if($sended){
		?>

		<div>
			<h3><?php echo "[x] Sent \n"; ?></h3>
		</div>

		<?php
	}
?>