<?php

require_once 'functions.php';
require_once 'db_config.php';

session_start();

$is_mainpage = true;
$is_auth = false;
$user_name = '';
$user_avatar = '';

$page_title = 'YetiCave';
$categories = [];

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


$sql = "SELECT DISTINCT `lots`.`id`, `lots`.`name`, `start_price`, `picture`, MAX(IF(`amount` IS NULL, `start_price`, `amount`)) AS `price`, COUNT(`lot`) AS `bids_number`, `categories`.`name` AS `category_name`, `creation_date`, `lots`.`end_date` "
    . "FROM `lots` "
    . "LEFT JOIN `bids` ON `lots`.`id` = `bids`.`lot` "
    . "INNER JOIN `categories` ON `lots`.`category` = `categories`.`id` "
    . "WHERE CURRENT_TIMESTAMP() < `end_date` "
    . "GROUP BY `lots`.`id`, `lots`.`name`, `start_price`, `picture`, `creation_date`, `category` "
    . "ORDER BY `creation_date` DESC;";

$result = mysqli_query($db_conf, $sql);

if (!$result) {
  $error = mysqli_error($db_conf);
  $page_content = '<p>Ошибка MySQL: ' . $error . '</p>';
} else {
  $goods = mysqli_fetch_all($result, MYSQLI_ASSOC);
  $page_content = renderTemplate('templates/index.php', [
    'categories' => $categories,
    'goods' => $goods
  ]);
}


$sql = "SELECT * FROM `lots` "
    . "WHERE `lots`.`winner` IS NULL "
    . "AND NOW() >= `lots`.`end_date`;";

$result = mysqli_query($db_conf, $sql);

if (!$result) {
  $errors['sendmail_sqlget_lots_nowinner'] = mysqli_error($db_conf);
} else {
  $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

  require_once 'getwinner.php';
}


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
