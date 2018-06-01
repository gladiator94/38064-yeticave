<section class="lot-item container">
  <h2><?= $lot['name']; ?></h2>
  <div class="lot-item__content">
    <div class="lot-item__left">
      <div class="lot-item__image">
        <img src="img/uploads/<?= $lot['picture']; ?>" width="730" height="548" alt="Сноуборд">
      </div>
      <p class="lot-item__category">Категория: <span><?= $lot['category_name']; ?></span></p>
      <p class="lot-item__description"><?= $lot['description']; ?></p>
    </div>
    <div class="lot-item__right">
    
      <div class="lot-item__state">
        <div class="lot-item__timer timer">
          <?= $end_time; ?>
        </div>
        <div class="lot-item__cost-state">
          <div class="lot-item__rate">
            <span class="lot-item__amount">Текущая цена</span>
            <span class="lot-item__cost"><?= formatPrice($lot['price']); ?> <b class="rub">р</b></span>
          </div>
          <div class="lot-item__min-cost">
            Мин. ставка <span><?= formatPrice(minBet($bids_count, $lot['price'], $lot['bet_step'])); ?> <b class="rub">р</b></span>
          </div>
        </div>
        <?php if ($is_auth && !$lot_expired && !$is_author): ?>
        <form class="lot-item__form" action="" method="post">
          <?php $form_item = formatFormItem($errors['bet']); ?>
          <p class="lot-item__form-item<?= $form_item['classname']; ?>">
            <label for="cost">Ваша ставка</label>
            <input id="cost" type="number" name="cost" placeholder="<?= formatPrice(minBet($bids_count, $lot['price'], $lot['bet_step'])); ?>" value="<?= $form_item['value']; ?>">
            <?= $form_item['error']; ?>
          </p>
          <button type="submit" class="button">Сделать ставку</button>
        </form>
        <?php endif; ?>
      </div>
      <div class="history">
        <h3>История ставок (<span><?= $bids_count; ?></span>)</h3>
        <table class="history__list">
        <?php foreach ($bids as $key => $bet): ?>
          <tr class="history__item">
            <td class="history__name"><?= $bet['name']; ?></td>
            <td class="history__price"><?= formatPrice($bet['amount']); ?> р</td>
            <td class="history__time"><?= $bet['date']; ?></td>
          </tr>
        <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>
</section>
