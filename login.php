<?php
require_once "functions.php";
require_once "db_config.php";

session_start();

$page_title = "Вход";

$categories = [];
$errors_post = [];


date_default_timezone_set("Europe/Moscow");

$db_conf = mysqli_connect($db_host, $db_user, $db_password, $db_name);

if (!$db_conf) {
  $error = "Ошибка подключения: " . mysqli_connect_error();
  $page_content = "<p>Ошибка MySQL: " . $error . "</p>";

  $layout_content = renderTemplate("templates/login_layout.php", ['page_title' => $page_title, 'categories' => $categories, 'content' => $page_content]);
  print($layout_content);

  exit(1);
}

mysqli_set_charset($db_conf, "utf8");

$sql = "SELECT `categories`.`id`, `categories`.`name` "
    . "FROM `categories` "
    . "ORDER BY `categories`.`id` ASC";

$result = mysqli_query($db_conf, $sql);
if (!$result) {
  $error = mysqli_error($db_conf);
  $categories['errors']['name'] = "<p>Ошибка MySQL: " . $error . "</p>";
} else {
  $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $login = $_POST['login'];

  $required = ['email', 'password'];
  $errors_disc = [
    'email' => "Введите e-mail",
    'password' => "Введите пароль"
  ];
  foreach ($required as $field) {
    if (empty($_POST['login'][$field])) {
      $errors_post[$field] = $errors_disc[$field];
    }
  }

  if (count($errors_post)) {
    $page_content = renderTemplate("templates/login_index.php", ['errors' => $errors_post, 'user' => $login]);
  } else {
    $safe_email = mysqli_real_escape_string($db_conf, $_POST['login']['email']);

    $sql = "SELECT * FROM `users` "
        . "WHERE `users`.`email` = '$safe_email'";

    $result = mysqli_query($db_conf, $sql);

    if (!$result) {
      $error = mysqli_error($db_conf);
      $page_content = "<p>Ошибка MySQL: " . $error . "</p>";

      $layout_content = renderTemplate("templates/login_layout.php", ['page_title' => $page_title, 'categories' => $categories, 'content' => $page_content]);
      print($layout_content);
      exit(1);
    }

    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if (password_verify($login['password'], $user['password']) and $user) {
      $_SESSION['user'] = $user;
      header("Location: /");
      exit();
    } else {
      $errors_post['password'] = "Пара E-mail-Пароль неверна";
      $page_content = renderTemplate("templates/login_index.php", ['errors' => $errors_post, 'user' => $login]);
    }
  }
} else {
  $page_content = renderTemplate("templates/login_index.php", ['errors' => $errors_post]);
}

$layout_content = renderTemplate("templates/login_layout.php", ['page_title' => $page_title, 'categories' => $categories, 'content' => $page_content]);
print($layout_content);

?>
