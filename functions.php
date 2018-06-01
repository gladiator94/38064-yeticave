<?php
/**
 * @param string $end_date дата окончания лота
 * @author Nikolay Dumchev
 * @copyright 2018 Wikipedia
 * @return string $date отформатированная строка указывающая количество дней, часов и минут
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
 * 
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
 * @param string $price цена
 * @author Nikolay Dumchev
 * @copyright 2018 Wikipedia
 * @return string $price_formatted отформатированная строка с ценой и знаком рубля
 */
function format_price($price)
{
  $price = htmlspecialchars($price);
  $price_formatted = ceil($price);

  if ($price_formatted > 999) {
    $price_formatted = number_format($price_formatted, 0, '', ' ');
  }

  $price_formatted = $price_formatted . ' <b class="rub">р</b>';

  return $price_formatted;
}

function formatPrice(string $price)
{
	$price = htmlspecialchars($price);
	$price_formatted = ceil($price);

  if ($price_formatted > 999) {
  	$price_formatted = number_format($price_formatted, 0, '', ' ');
  }

  return $price_formatted;
}

/**
 * @param float $price цена лота
 * @author Nikolay Dumchev
 * @copyright 2018 Wikipedia
 * @return string $price_formatted отформатированная строка с ценой и БЕЗ знака рубля
 */
function format_price__without_r($price)
{
  $price = htmlspecialchars($price);
  $price_formatted = ceil($price);

  if ($price_formatted > 999) {
    $price_formatted = number_format($price_formatted, 0, '', ' ');
  }

  return $price_formatted;
}

/**
 * @param int $bids_count количество ставок
 * @param float $price цена лота
 * @param float $bet_step минимальный шаг ставки
 * @author Nikolay Dumchev
 * @copyright 2018 Wikipedia
 * @return float $min_price минимальная ставка которую можно сделать
 */
function min_bet($bids_count, $price, $bet_step)
{
	$price = htmlspecialchars($price);
	$min_price = $price;

	if ($bids_count > 0) {
		$bet_step = htmlspecialchars($bet_step);
		$min_price = ceil($price) + ceil($bet_step);
	}

	return $min_price;
}

/**
 * @param string $templatePath путь к сценарию шаблона
 * @param array $templateData данные необходимые для рендеринга шаблона
 * @author Nikolay Dumchev
 * @copyright 2018 Wikipedia
 * @return string $content html-код шаблона
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
 * @param string $error текст ошибки
 * @param string $error значение поля, если есть
 * @author Nikolay Dumchev
 * @copyright 2018 Wikipedia
 * @return array $format массив содержащий в себе название класса (для отрисовки ошибки для поля формы), текст ошибки, значение поля, которое было передано через POST
 */
function formatFormItem($error, $value = null)
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
