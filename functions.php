<?php
/**
 * Формирует количество дней, часов, минут и секунд
 * до завершения аукциона лота, и
 * форматирует время полученное из подготовленной строки
 * в формат "%количество% дней H:i:s"
 * @param string $end_date дата окончания лота
 *
 * @return string $date отформатированная строка в формате %количество% дней H:i:s
 */
function timeLot(string $end_date)
{
	$date = '';
	$time_lot = strtotime($end_date) - time();

	if ($time_lot <= 0) {
		$date = date('H:i:s', strtotime('00:00:00'));
	} else {
		$time_days = floor($time_lot / 86400);
		$time_hours = floor(($time_lot % 86400) / 3600);
		$time_minutes = floor((($time_lot % 86400) % 3600) / 60);
		$time_seconds = floor((($time_lot % 86400) % 3600) % 60);

		$get_time = $time_hours . ':' . $time_minutes . ':' . $time_seconds;

		$word = formatWordDays(intval($time_days));

		$date = date($time_days . html_entity_decode('&nbsp;') . $word . ' H:i:s', strtotime($get_time));
	}

	return $date;
}

/**
 * Проверяют осталось ли меньше часа до окончания времени лота
 * @param string $end_date дата окончания лота
 *
 * @return bool $check false, если больше часа, true если меньше
 */
function timeFinishing(string $end_date)
{
	$check = false;

	$time_lot = strtotime($end_date) - time();
	if ($time_lot < 3600) {
		$check = true;
	}
	
	return $check;
}

/**
 * Форматирует строку с ценой добавляя пробелы
 * через каждые 3 цифры
 * @param string $price строка с ценой
 *
 * @return string $price_formatted отформатированная строка с ценой
 */
function formatPrice(string $price)
{
	$price = htmlspecialchars($price);
	$price_formatted = ceil((int) $price);

  if ($price_formatted > 999) {
  	$price_formatted = number_format($price_formatted, 0, '', ' ');
  }

  return $price_formatted;
}

/**
 * Подсчитывает минимальную ставку, которую может сделать пользователь
 * @param string $bids_count строка с количеством ставок
 * @param string $price строка с ценой
 * @param string $bet_step строка шагом ставки
 *
 * @return string $min_price строка с минимальной ставкой
 */
function minBet($bids_count, $price, $bet_step)
{
	$price = htmlspecialchars($price);
	$min_price = $price;

	if ($bids_count > 0) {
		$bet_step = htmlspecialchars($bet_step);
		$min_price = ceil($price) + ceil((int) $bet_step);
	}

	return (string) $min_price;
}

/**
 * Функция получает код из указаного файла (шаблона)
 * и передает в переменную
 * @param string $templatePath путь к подключаемому файлу
 * @param array $templateData массив с данным необходимыми для рендеринга шаблона
 *
 * @return string $content контент полученный из подключенного файла
 */
function renderTemplate(string $templatePath, array $templateData = [])
{
	$content = '';
	extract($templateData);

	if (file_exists($templatePath)) {
		ob_start();
		require($templatePath);
		$content = ob_get_contents();
		ob_end_clean();
	}

	return $content;
}

/**
 * Функция формирует массив с названием класса,
 * html-кодом для вывода ошибки и значением
 * для элементов форм
 * @param string $error текст ошибки
 * @param string $value значение поля формы
 *
 * @return array $format массив с данным для подстановки в шаблон элемента формы
 */
function formatFormItem($error, $value = '')
{
	$value = htmlspecialchars($value);

	$format = [
		'classname' => '',
		'error' => '',
		'value' => ''
	];

	if (isset($error)) {
		$format['classname'] = ' form__item--invalid';
		$format['error'] = '<span class="form__error">' . $error . '</span>';
	}

	if (isset($value)) {
		$format['value'] = $value;
	}

	return $format;
}

/**
 * Склоняет слово 'ставка' в родительный падеж и необходимое
 * число в зависимости от количества ставок
 * @param int $bids_count количество ставок
 *
 * @return string $word слово 'ставки' в нужном склонении
 */
function formatWordBids(int $bids_count)
{
	$word = 'ставок';

	$bids = $bids_count % 10;
	$bids_2 = $bids_count % 100;

	if ($bids === 1) {
		$word = 'ставка';
	} else if ($bids >= 2 && $bids <= 4) {
		$word = 'ставки';
	}

	if ($bids_2 >= 10 && $bids_2 <= 14) {
		$word = 'ставок';
	}

	return $word;
}

/**
 * Склоняет слово 'день' в родительный падеж и необходимое
 * число в зависимости от количества дней
 * @param int $days_count количество дней
 *
 * @return string $word слово 'день' в нужном склонении
 */
function formatWordDays(int $days_count)
{
	$word = 'дней';

	$days = $days_count % 10;
	$days_2 = $days_count % 100;

	if ($days === 1) {
		$word = 'день';
	} else if ($days >= 2 && $days <= 4) {
		$word = 'дня';
	}

	if ($days_2 >= 10 && $days_2 <= 14) {
		$word = 'дней';
	}

	return $word;
}

/**
 * Получает из указанной в $db_conf БД из таблицы `categories`
 * массив со списком id и названиями категорий
 * @param object $db_conf объект функции  mysqli_connect
 *
 * @return array $categories массив со списком категорий или массив ошибкой в случае ошибки в исполнении запроса
 */
function getCategories(object $db_conf)
{
	$sql = "SELECT `categories`.`id`, `categories`.`name` "
    . "FROM `categories` "
    . "ORDER BY `categories`.`id` ASC";

  $result = mysqli_query($db_conf, $sql);

  if (!$result) {
  	$error = mysqli_error($db_conf);
  	$categories['errors']['name'] = '<p>Ошибка MySQL: ' . $error . '</p>';
	} else {
  	$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
	}

  return $categories;
}
