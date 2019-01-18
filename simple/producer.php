<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;


$sended = false;
if(isset($_POST['client'])){
	
	$message = filter_input(INPUT_POST, "client", FILTER_SANITIZE_STRING);


	$connection = new AMQPConnection("localhost",5672,'guest','guest');

	$channel = $connection->channel();

	$channel->queue_declare(
            'pizzaTime',    #queue name - Имя очереди может содержать до 255 байт UTF-8 символов
            false,          #passive - может использоваться для проверки того, инициирован ли обмен, без того, чтобы изменять состояние сервера
            true,          #durable - убедимся, что RabbitMQ никогда не потеряет очередь при падении - очередь переживёт перезагрузку брокера
            false,          #exclusive - используется только одним соединением, и очередь будет удалена при закрытии соединения
            false           #autodelete - очередь удаляется, когда отписывается последний подписчик
    );

	$msg = new AMQPMessage($message,['delivery_mode'=>2]);

	$channel->basic_publish(
            $msg,           #message
            '',             #exchange
            'pizzaTime'     #routing key
    );

	$channel->close();
	$connection->close();

	$sended = true;
}

?>

<div>
	<h3>I want to pizza</h3>
	<form action="" method="POST">
		<input type="text" name="client" required>
		<input type="submit" value="GET">
	</form>
</div>

<?php 
	if($sended){
		?>

		<div>
			<h3><?php echo $message; ?>,Wait your pizza!</h3>
		</div>

		<?php
	}
?>