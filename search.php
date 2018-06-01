<?php

require_once 'functions.php';
require_once 'db_config.php';

session_start();

$is_mainpage = false;
$is_auth = false;
$user_name = '';
$user_avatar = '';

$page_title = 'Результаты поиска по запросу';
$categories = [];
$lots = [];
$errors = [];
$lots_pagination = 9;
$no_lots = true;
$no_pagination = true;

if (isset($_SESSION['user'])) {
  $is_auth = true;
  $user_name = $_SESSION['user']['name'];
  $user_avatar = $_SESSION['user']['avatar'] ? 'img/uploads/users/' . $_SESSION['user']['avatar']: 'img/user_default.png';
}


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


$search = mysqli_real_escape_string($db_conf, $_GET['search']);
$page_title .= $page_title . ' ' . $search;

$sql = "SELECT `lots`.`id`, `lots`.`name`, `lots`.`description`, `categories`.`name` AS `category_name`, `lots`.`picture`, `lots`.`end_date`, `bids_count`.`count`, "
		. "IF (`bids`.`lot` IS NULL, `lots`.`start_price`, MAX(`bids`.`amount`)) AS `price` "
		. "FROM `lots` "
		. "INNER JOIN `categories` "
		. "ON `categories`.`id` = `lots`.`category` "
		. "LEFT JOIN (SELECT COUNT(`bids`.`lot`) AS `count`, `bids`.`lot` "
			. "FROM `bids` "
			. "INNER JOIN `lots` ON `bids`.`lot` = `lots`.`id` "
			. "WHERE `bids`.`lot` = `lots`.`id` "
			. "GROUP BY `bids`.`lot`) AS `bids_count` "
		. "ON `lots`.`id` = `bids_count`.`lot` "
		. "LEFT JOIN `bids` ON `lots`.`id` = `bids`.`lot` "
		. "WHERE MATCH(`lots`.`name`, `lots`.`description`) AGAINST('$search') "
		. "GROUP BY `lots`.`id`;";

$result = mysqli_query($db_conf, $sql);
$lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

if ($lots) {
	$no_lots = false;

	foreach ($lots as $key => $lot) {
		if (!empty($lot['count'])) {
			$lots[$key]['count'] = $lot['count'] . ' ' . formatWordBids((int) $lot['count']);
		} else {
			$lots[$key]['count'] = 'Стартовая цена';
		}

		$lots[$key]['time_left'] = timeLot($lot['end_date']);

		if (timeFinishing($lot['end_date'])) {
			$lots[$key]['time_finishing'] = true;
		}
	}
}

$page_content = renderTemplate('templates/search_index.php', [
	'lots' => $lots,
	'search' => $search
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
