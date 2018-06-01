<?php

require_once 'functions.php';
require_once 'db_config.php';

session_start();

$is_mainpage = false;
$is_auth = false;
$user_name = '';
$user_avatar = '';

if (isset($_SESSION['user'])) {
  $is_auth = true;
  $user_name = $_SESSION['user']['name'];
  $user_avatar = $_SESSION['user']['avatar'] ? 'img/uploads/users/' . $_SESSION['user']['avatar']: 'img/user_default.png';
}

$page_title = 'Лот';
$categories = [];
$errors_post = [];

$lot_expired = true;
$is_author = true;


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


if (!isset($_GET['id'])) {
  http_response_code(404);
  $page_content = renderTemplate('templates/lot_404.php', []);

  $layout_content = renderTemplate('templates/layout.php', [
    'page_title' => $page_title,
    'is_mainpage' => $is_mainpage,
    'categories' => $categories,
    'content' => $page_content
  ]);

  print($layout_content);
  exit(1);
}


$lot_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (!$is_auth) {
    $errors_post['bet'] = 'Авторизуйтесь, чтобы делать ставки';
  } else {
    $bet = $_POST['cost'];

    if (empty($bet)) {
      $errors_post['bet'] = 'Укажите вашу ставку';
    } else if (!filter_var($bet, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
      $errors_post['bet'] = 'Ставка должна быть целым числом больше 0';
    } else {

      $sql = "SELECT `lots`.`id`, IF(`bids`.`lot` IS NULL, `lots`.`start_price`, MAX(`bids`.`amount`)) AS `price`, `lots`.`bet_step` "
          . "FROM `lots` "
          . "LEFT JOIN `bids` ON `lots`.`id` = `bids`.`lot` "
          . "WHERE `lots`.`id` = '$lot_id' "
          . "GROUP BY `lots`.`id`;";

      $result = mysqli_query($db_conf, $sql);

      if (!$result) {
        $error = mysqli_error($db_conf);
        $errors_post['bet'] = '<p>Ошибка MySQL: ' . $error . '</p>';
      } else {
        $lot_bet = mysqli_fetch_assoc($result);
    
        $sql = "SELECT COUNT(`bids`.`lot`) AS `bids_count` "
            . "FROM `lots` "
            . "LEFT JOIN `bids` ON `lots`.`id` = `bids`.`lot` "
            . "WHERE `lots`.`id` = '$lot_id';";

        $result = mysqli_query($db_conf, $sql);

        if (!$result) {
          $error = mysqli_error($db_conf);
          $errors_post['bet'] = '<p>Ошибка MySQL: ' . $error . '</p>';
        } else {
          $bids_count = intval(mysqli_fetch_assoc($result)['bids_count']);

          $min_bet = ($bids_count > 0) ? (intval($lot_bet['price']) + intval($lot_bet['bet_step'])) : intval($lot_bet['price']);

          if ($bet < $min_bet) {
            $errors_post['bet'] = 'Минимально возможная ставка: ' . format_price__without_r($min_bet);
          }
        }
      }
    }
  }

  if (!count($errors_post)) {
    $user_id = $_SESSION['user']['id'];
    $lot_id = $lot_bet['id'];

    $sql = "INSERT INTO `bids` (`date`, `amount`, `user`, `lot`) "
        . "VALUES (NOW(), '$bet', '$user_id', '$lot_id');";

    $result = mysqli_query($db_conf, $sql);

    if (!$result) {
      $error = mysqli_error($db_conf);
      $errors_post['bet'] = '<p>Ошибка MySQL: ' . $error . '</p>';
    } else {
      $current_url = $_SERVER['REQUEST_URI'];
      header('Location: ' . $current_url);
    }
  }
}


$sql = "SELECT `lots`.`id`, `lots`.`name`, `lots`.`description`, `lots`.`picture`, `lots`.`end_date`, `lots`.`start_price`, IF(`bids`.`lot` IS NULL, `lots`.`start_price`, MAX(`bids`.`amount`)) AS `price`, `lots`.`bet_step`, `lots`.`author`, `categories`.`name` AS `category_name` "
    . "FROM `lots` "
    . "INNER JOIN `categories` ON `lots`.`category` = `categories`.`id` "
    . "LEFT JOIN `bids` ON `lots`.`id` = `bids`.`lot` "
    . "WHERE `lots`.`id` = '$lot_id' "
    . "GROUP BY `lots`.`id`;";

$result = mysqli_query($db_conf, $sql);

if (!$result) {
  $error = mysqli_error($db_conf);
  $page_content = '<p>Ошибка MySQL: ' . $error . '</p>';
} else if (!mysqli_num_rows($result)) {
  http_response_code(404);
  $page_content = renderTemplate('templates/lot_404.php', []);
} else {
  $lot = mysqli_fetch_assoc($result);

  $page_title = $lot['name'];

  $sql = "SELECT COUNT(`bids`.`lot`) AS `bids_count` "
      . "FROM `lots` "
      . "LEFT JOIN `bids` ON `lots`.`id` = `bids`.`lot` "
      . "WHERE `lots`.`id` = '$lot_id';";

  $result = mysqli_query($db_conf, $sql);

  if (!$result) {
    $error = mysqli_error($db_conf);
    $bids_count = '<p>Ошибка MySQL: ' . $error . '</p>';
  } else {
    $bids_count = mysqli_fetch_assoc($result)['bids_count'];
  }

  $sql = "SELECT `bids`.`date`, `bids`.`amount`, `users`.`name` "
      . "FROM `bids` "
      . "INNER JOIN `users` ON `bids`.`user` = `users`.`id` "
      . "WHERE `bids`.`lot` = '$lot_id' "
      . "ORDER BY `bids`.`date` DESC;";

  $result = mysqli_query($db_conf, $sql);

  if (!$result) {
    $error = mysqli_error($db_conf);
    $bids['errors']['name'] = '<p>Ошибка MySQL: ' . $error . '</p>';
  } else {
    $bids = mysqli_fetch_all($result, MYSQLI_ASSOC);
  }

  $end_time = timeLot($lot['end_date']);

  if (strtotime($lot['end_date']) > time()) {
    $lot_expired = false;
  }

  if ($lot['author'] !== $_SESSION['user']['id']) {
    $is_author = false;
  }

  $page_content = renderTemplate('templates/lot_index.php', [
    'lot' => $lot,
    'bids' => $bids,
    'bids_count' => $bids_count,
    'is_auth' => $is_auth,
    'lot_expired' => $lot_expired,
    'is_author' => $is_author,
    'end_time' => $end_time,
    'errors' => $errors_post
  ]);
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
