<div class="container">
  <section class="lots">
    <h2>Все лоты<?php
    if ($category_name): ?> в категории <span>«<?= $category_name; ?>»</span>
    <?php endif; ?></h2>
    <?php if ($no_lots): ?>
    <p>Лотов в данной категории на данный момент <b>нет</b></p>
    <?php else: ?>
    <ul class="lots__list">
    <?php foreach ($lots as $key => $lot):
      $classname = isset($lot['time_finishing']) ? ' timer--finishing' : ''; ?>
      <li class="lots__item lot">
        <div class="lot__image">
          <img src="img/uploads/<?= $lot['picture']; ?>" width="350" height="260" alt="<?= $lot['name']; ?>">
        </div>
        <div class="lot__info">
          <span class="lot__category"><?= $lot['category_name']; ?></span>
          <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $lot['id']; ?>"><?= htmlspecialchars($lot['name']); ?></a></h3>
          <div class="lot__state">
            <div class="lot__rate">
              <span class="lot__amount"><?= $lot['count']; ?></span>
              <span class="lot__cost"><?= formatPrice($lot['price']); ?><b class="rub">р</b></span>
            </div>
            <div class="lot__timer timer<?= $classname; ?>"><?= $lot['time_left']; ?></div>
          </div>
        </div>
      </li>
    <? endforeach; ?>
    </ul>
    <? endif; ?>
  </section>
  <?php if (!$no_pagination): ?>
  <ul class="pagination-list">
    <li class="pagination-item pagination-item-prev"><a<?php
      if ($pages_count > 1 && $cur_page !== 1): ?> href="<?php
        if (isset($category_id)): ?>?category=<?= $category_id; ?>&<?php
        else: ?>?<?php
        endif; ?>page=<?= ($cur_page - 1); ?>" <?php endif; ?>>Назад</a>
    </li>
  <?php foreach ($pages as $page): ?>
    <li class="pagination-item<?php
      if ($page === $cur_page): ?> pagination-item-active<?php
      endif; ?>">
      <a href="<?php
        if (isset($category_id)): ?>?category=<?= $category_id; ?>&<?php
        else: ?>?<?php
        endif; ?>page=<?= $page; ?>"><?= $page; ?></a>
    </li>
  <?php endforeach; ?>
    <li class="pagination-item pagination-item-next"><a<?php
     if ($pages_count > 1 && $cur_page !== (count($pages))): ?> href="<?php
      if (isset($category_id)): ?>?category=<?= $category_id; ?>&<?php
      else: ?>?<?php
      endif; ?>page=<?= ($cur_page + 1); ?>" <?php endif; ?>>Вперед</a></li>
  </ul>
  <?php endif; ?>
</div>
