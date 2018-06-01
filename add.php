<?php

require_once 'functions.php';
require_once 'db_config.php';

session_start();

$is_mainpage = false;
$is_auth = false;
$user_name = '';
$user_avatar = '';

$page_title = 'Добавление лота';
$categories = [];
$errors_post = [];

if (isset($_SESSION['user'])) {
  $is_auth = true;
  $user_name = $_SESSION['user']['name'];
  $user_avatar = $_SESSION['user']['avatar'] ? 'img/uploads/users/' . $_SESSION['user']['avatar']: 'img/user_default.png';
} else {
  http_response_code(403);

  $page_content = renderTemplate('templates/add_403.php', []);

  $layout_content = renderTemplate('templates/layout.php', [
    'page_title' => $page_title,
    'is_mainpage' => $is_mainpage,
    'categories' => $categories,
    'content' => $page_content
  ]);

  print($layout_content);
  exit(0);
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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lot = $_POST['lot'];

  $required = ['title', 'category', 'description', 'start_price', 'bet_step', 'end_date'];
  $errors_disc = [
    'title' => 'Введите наименование лота',
    'category' => 'Выберите категорию',
    'description' => 'Напишите описание лота',
    'start_price' => 'Введите начальную цену',
    'bet_step' => 'Введите шаг ставки',
    'end_date' => 'Укажите дату завершения торгов',
    'price_negative' => 'Укажите целое число больше 0',
    'date_incorrect' => 'Укажите дату в корректном формате',
    'date_expired' => 'Дата окончания торгов должна быть больше текущей даты хотя бы на один день',
    'picture' => 'Загрузите изображение лота',
    'picture_format' => 'Загрузите изображение в формате jpg/jpeg или png',
    'picture_size' => 'Загружаемое изображение не должно превышать размеров 1024x768'
  ];

  foreach ($required as $field) {
    if (empty($lot[$field])) {
      $errors_post[$field] = $errors_disc[$field];
    } else {
      switch ($field) {
        case 'start_price':
          if (!filter_var($lot[$field], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $errors_post[$field] = $errors_disc['price_negative'];
          }
          break;
        
        case 'bet_step':
          if (!filter_var($lot[$field], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $errors_post[$field] = $errors_disc['price_negative'];
          }
          break;

        case 'end_date':
          $end_date = date_create_from_format('Y-m-d', $lot['end_date']);

          if (!$end_date) {
            $errors_post[$field] = $errors_disc['date_incorrect'];
          } else {
            date_time_set($end_date, 0, 0, 0);

            $now_date = date_create("now");
            date_time_set($now_date, 0, 0, 0);

            $diff = date_diff($end_date, $now_date);

            $utm_end_date = date_timestamp_get($end_date);
            $utm_diff_date = date_timestamp_get($now_date);

            if (!(($utm_end_date > $utm_diff_date) && (($diff->d >= 1) || ($diff->m >= 1) || ($diff->y >= 1)))) {
              $errors_post[$field] = $errors_disc['date_expired'];
            }
          }
          break;

        default:
          break;
      }
    }
  }

  if (empty($_FILES['picture']['name'])) {
    $errors_post['picture'] = $errors_disc['picture'];
  } else {
    $tmp_name = htmlspecialchars($_FILES['picture']['tmp_name']);
    $name = htmlspecialchars($_FILES['picture']['name']);

    $file_type = mime_content_type($tmp_name);

    if (!($file_type === 'image/jpeg' || $file_type === 'image/png')) {
      $errors_post['picture'] = $errors_disc['picture_format'];
    } else {
      list($img_width, $img_height) = getimagesize($_FILES['picture']['tmp_name']);

      if ($img_width > 1024 || $img_height > 768) {
        $errors_post['picture'] = $errors_disc['picture_size'];
      }
    }
  }

  if (count($errors_post)) {
    $page_content = renderTemplate('templates/add_index.php', [
      'lot' => $lot,
      'errors' => $errors_post,
      'categories' => $categories
    ]);
  } else {
    $upload_path = __DIR__ . '/img/uploads/';
    $extension = pathinfo($name, PATHINFO_EXTENSION);
    $uniq_name = bin2hex(random_bytes(16)) . '.' . $extension;

    if (move_uploaded_file($tmp_name, $upload_path . $uniq_name)) {
      $author_id = $_SESSION['user']['id'];

      $sql = "INSERT INTO `lots` (`name`, `description`, `picture`, `creation_date`, `end_date`, `start_price`, `bet_step`, `author`, `category`) "
          . "VALUES (?, ?, ?, NOW(), ?, ?, ?, '$author_id', ?);";

      $stmt = mysqli_prepare($db_conf, $sql);
      mysqli_stmt_bind_param($stmt, 'ssssiii', htmlspecialchars($lot['title']), htmlspecialchars($lot['description']), $uniq_name, htmlspecialchars($lot['end_date']), htmlspecialchars($lot['start_price']), htmlspecialchars($lot['bet_step']), htmlspecialchars($lot['category']));
      $result = mysqli_stmt_execute($stmt);

      if (!$result) {
        $error = mysqli_error($db_conf);
        $page_content = '<p>Ошибка MySQL: ' . $error . '</p>';
      } else {
        $lot_id = mysqli_insert_id($db_conf);
        header('Location: lot.php?id=' . $lot_id);
        exit(0);
      }
    } else {
      $page_content = '<p>Файл не может быть записан на сервер. Пожалуйста, обратитесь к администратору сайта</p>';
    }
  }
} else {
  $page_content = renderTemplate('templates/add_index.php', [
    'lot' => $lot,
    'errors' => $errors_post,
    'categories' => $categories
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
