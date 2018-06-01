<?php

require_once 'vendor/autoload.php';

$site_email = 'keks@phpdemo.ru';

$transport = new Swift_SmtpTransport('phpdemo.ru', 25);
$transport->setUsername('keks@phpdemo.ru');
$transport->setPassword('htmlacademy');
$mailer = new Swift_Mailer($transport);

foreach ($lots as $key => $lot) {
	$lot_id = $lot['id'];

	$sql = "SELECT * "
			. "FROM `bids` "
			. "JOIN (SELECT MAX(`bids`.`date`) AS `bids_last`, `bids`.`lot` "
				. "FROM `bids` "
				. "WHERE `bids`.`lot` = '$lot_id' "
				. "GROUP BY `bids`.`lot`) AS lot_last "
			. "ON `bids`.`lot` = `lot_last`.`lot` AND `bids`.`date` = `lot_last`.`bids_last`;";

	$result = mysqli_query($db_conf, $sql);

	if (!$result) {
		$errors['sendmail_sqlget_last_bet'] = mysqli_error($db_conf);
	}
	
	$bet = mysqli_fetch_assoc($result);
	$winner = $bet['user'];

	$sql = "UPDATE `lots` "
			. "SET `lots`.`winner` = '$winner' "
			. "WHERE `lots`.`id` = '$lot_id';";

	$result = mysqli_query($db_conf, $sql);

	if (!$result) {
		$errors['sendmail_sqlset_lot_winner'] = mysqli_error($db_conf);
	}


	$sql = "SELECT `users`.`email`, `users`.`name` "
			. "FROM `users` "
			. "WHERE `users`.`id` = '$winner';";

	$result = mysqli_query($db_conf, $sql);

	if (!$result) {
		$errors['sendmail_sqlget_winner_data'] = mysqli_error($db_conf);
	}

	$winner_data = mysqli_fetch_assoc($result);


	$sql = "SELECT `lots`.`name` FROM `lots` "
			. "WHERE `lots`.`id` = '$lot_id';";

	$result = mysqli_query($db_conf, $sql);
	$lot_name = mysqli_fetch_assoc($result)['name'];


	if (is_array($winner_data) and !empty($winner_data)) {
		$winner_email = $winner_data['email'];
		$winner_name = $winner_data['name'];

		$message = new Swift_Message('Ваша ставка победила');
		$message->setFrom([$site_email => 'YetiCave']);
		$message->setTo([$winner_email => $winner_name]);
		$message->setContentType('text/html');

		$meesage_body = renderTemplate('templates/email.php', [
			'user_name' => $winner_data['name'],
			'lot_id' => $lot_id,
			'lot_name' => $lot_name
		]);

		$message->setBody($meesage_body);

		$mailer->send($message);
	}
}
