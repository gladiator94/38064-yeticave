<div class="container">
  <section class="lots">
    <h2>Результаты поиска по запросу «<span><?= $search; ?></span>»</h2>
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
  </section>
</div>
