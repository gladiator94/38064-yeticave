<?php

require_once 'functions.php';
require_once 'db_config.php';

session_start();

$is_mainpage = false;
$is_auth = false;
$user_name = '';
$user_avatar = '';

$page_title = 'Мои ставки';
$categories = [];


date_default_timezone_set('Europe/Moscow');

$db_conf = mysqli_connect($db_host, $db_user, $db_password, $db_name);

if (!$db_conf) {
  $error = 'Ошибка подключения: ' . mysqli_connect_error();
  $page_content = '<p>Ошибка MySQL: ' . $error . '</p>';

  $layout_content = renderTemplate('templates/layout.php', [
    'page_title' => $page_title,
    'is_mainpage' => $is_mainpage,
    'categories' => $categories,
    'content' => $page_content
  ]);

  print($layout_content);
  exit(1);
}

mysqli_set_charset($db_conf, 'utf8');

$categories = getCategories($db_conf);

if (isset($_SESSION['user'])) {
  $is_auth = true;
  $user_name = $_SESSION['user']['name'];
  $user_avatar = $_SESSION['user']['avatar'] ? 'img/uploads/users/' . $_SESSION['user']['avatar']: 'img/user_default.png';
  $user_id = $_SESSION['user']['id'];

  $sql = "SELECT `lots`.`id`, `lots`.`name`, `categories`.`name` AS `category_name`, `lots`.`end_date`, `bids`.`amount`, `bids`.`date`, `lots`.`picture`, `lots`.`winner` "
			. "FROM `bids` "
			. "INNER JOIN `lots` ON `lots`.`id` = `bids`.`lot` "
			. "INNER JOIN `categories` ON `lots`.`category` = `categories`.`id` "
			. "WHERE `bids`.`user` = '$user_id';";

	$result = mysqli_query($db_conf, $sql);
	$bids = mysqli_fetch_all($result, MYSQLI_ASSOC);

	foreach ($bids as $key => $bet) {
		if ($bet['winner'] === $user_id) {
			$bids[$key]['win'] = true;
			$bids[$key]['time_left'] = 'Ставка выиграла';

		} else if (strtotime($bet['end_date']) <= time()) {
			$bids[$key]['time_end'] = true;
			$bids[$key]['time_left'] = 'Торги окончены';

		} else {
			$bids[$key]['time_left'] = timeLot($bet['end_date']);

			if (timeFinishing($bet['end_date'])) {
				$bids[$key]['time_finishing'] = true;
			}
		}
	}

  $page_content = renderTemplate('templates/my-lots_index.php', [
  	'bids' => $bids
	]);

	$layout_content = renderTemplate('templates/layout.php', [
  	'page_title' => $page_title,
  	'is_mainpage' => $is_mainpage,
  	'is_auth' => $is_auth,
  	'user_name' => $user_name,
  	'user_avatar' => $user_avatar,
  	'categories' => $categories,
  	'content' => $page_content
	]);

	print($layout_content);
	exit(0);
}


http_response_code(403);
$page_content = renderTemplate('templates/my-lots_403.php', []);

$layout_content = renderTemplate('templates/layout.php', [
 	'page_title' => $page_title,
 	'is_mainpage' => $is_mainpage,
 	'is_auth' => $is_auth,
 	'user_name' => $user_name,
 	'user_avatar' => $user_avatar,
 	'categories' => $categories,
 	'content' => $page_content
]);

print($layout_content);
