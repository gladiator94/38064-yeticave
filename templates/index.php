<section class="promo">
  <h2 class="promo__title">Нужен стафф для катки?</h2>
  <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
  <ul class="promo__list">
    <?php foreach ($categories as $key => $category): ?>
      <li class="promo__item<?php
        switch ($category['name']) {
          case 'Доски и лыжи':
            ?> promo__item--boards<?php
            break;

          case 'Доски и лыжи':
            ?> promo__item--attachment<?php
            break;

          case 'Крепления':
            ?> promo__item--boots<?php
            break;

          case 'Ботинки':
            ?> promo__item--boards<?php
            break;

          case 'Одежда':
            ?> promo__item--clothing<?php
            break;

          case 'Инструменты':
            ?> promo__item--tools<?php
            break;

          case 'Разное':
            ?> promo__item--other<?php
            break;

          default:
            break;
        }
      ?>">
      <a class="promo__link" href="category.php?category=<?= $category['id']; ?>"><?= $category['name']; ?></a>
    </li>
    <?php endforeach; ?>
  </ul>
</section>
<section class="lots">
  <div class="lots__header">
    <h2>Открытые лоты</h2>
  </div>
  <ul class="lots__list">
  <?php foreach ($goods as $key => $good): ?>
    <li class="lots__item lot">
      <div class="lot__image">
        <img src="img/uploads/<?= $good['picture']; ?>" width="350" height="260" alt="<?= $good['name']; ?>">
      </div>
      <div class="lot__info">
        <span class="lot__category"><?= htmlspecialchars($good['category_name']); ?></span>
        <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $good['id']; ?>"><?= htmlspecialchars($good['name']); ?></a></h3>
        <div class="lot__state">
          <div class="lot__rate">
            <span class="lot__amount">Стартовая цена</span>
            <span class="lot__cost"><?= formatPrice($good['price']); ?> <b class="rub">р</b></span>
          </div>
          <div class="lot__timer timer">
            <?= timeLot($good['end_date']); ?>
          </div>
        </div>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
</section>
  