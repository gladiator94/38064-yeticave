<section class="rates container">
  <h2>Мои ставки</h2>
  <table class="rates__list">
  <?php foreach ($bids as $key => $bet): ?>
    <tr class="rates__item<?php
      if (isset($bet['win'])): ?> rates__item--win<?php
      elseif (isset($bet['time_end'])):
      ?> rates__item--end<?php
      endif; ?>
    ?>">
      <td class="rates__info">
        <div class="rates__img">
          <img src="img/uploads/<?= $bet['picture']; ?>" width="54" height="40" alt="<?= $bet['name']; ?>">
        </div>
        <h3 class="rates__title"><a href="lot.php?id=<?= $bet['id']; ?>"><?= $bet['name']; ?></a></h3>
      </td>
      <td class="rates__category"><?= $bet['category_name']; ?></td>
      <td class="rates__timer">
        <div class="timer<?php if (isset($bet['time_finishing'])):
          ?> timer--finishing<?php
        endif;
        if (isset($bet['win'])): ?> timer--win<?php
        elseif (isset($bet['time_end'])):
          ?> timer--end<?php
        endif; ?>"><?= $bet['time_left']; ?></div>
      </td>
      <td class="rates__price"><?= formatPrice($bet['amount']); ?> р</td>
      <td class="rates__time"><?= $bet['date']; ?></td>
    </tr>
  <?php endforeach; ?>
  </table>
</section>
