<?php declare(strict_types=1);
$hasDates    = !empty($filters['start_date']) && !empty($filters['end_date']);
$hasCategory = !empty($filters['category_id']);
$hasBrand    = !empty($filters['brand']);
$hasPrice    = !empty($filters['max_price']);
$hasFilters  = $hasDates || $hasCategory || $hasBrand || $hasPrice;

$nbDays = 0;
if ($hasDates) {
    $s = new DateTimeImmutable($filters['start_date']);
    $e = new DateTimeImmutable($filters['end_date']);
    $nbDays = (int) $s->diff($e)->days;
}

function fmt(string $d): string {
    return (new DateTimeImmutable($d))->format('d/m/Y');
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h4 fw-bold mb-0">
        <i class="bi bi-search me-2 text-warning"></i>
        <?php if ($hasDates): ?>
            Véhicules disponibles du <span class="text-warning"><?= fmt($filters['start_date']) ?></span>
            au <span class="text-warning"><?= fmt($filters['end_date']) ?></span>
            <small class="text-muted fw-normal">(<?= $nbDays ?> jour<?= $nbDays > 1 ? 's' : '' ?>)</small>
        <?php else: ?>
            Véhicules disponibles
        <?php endif; ?>
    </h1>
    <span class="badge bg-dark fs-6"><?= count($vehicles) ?> résultat<?= count($vehicles) > 1 ? 's' : '' ?></span>
</div>

<?php if (!empty($dateError)): ?>
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
        <span><?= ($dateError) ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= ($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= ($success) ?></div>
<?php endif; ?>

<form method="GET" action="<?= APP_URL ?>/vehicles"
      class="card card-body mb-3 border-0 bg-white" style="box-shadow: 2px 2px 4px 1px #252525;"
      id="searchForm">

    <div class="row g-2 align-items-end mb-2">

        <div class="col-md-3">
            <label class="form-label small fw-semibold">
                <i class="bi bi-tag me-1 text-warning"></i>Catégorie
            </label>
            <select name="category_id" class="form-select form-select-sm">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= ((int)($raw['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                        <?= ($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="start_date" class="form-label small fw-semibold">
                <i class="bi bi-calendar-event me-1 text-warning"></i>Date de début
            </label>
            <input type="date" id="start_date" name="start_date"
                   class="form-control form-control-sm"
                   min="<?= date('Y-m-d') ?>"
                   value="<?= ($raw['start_date'] ?? '') ?>">
        </div>

        <div class="col-md-3">
            <label for="end_date" class="form-label small fw-semibold">
                <i class="bi bi-calendar-check me-1 text-warning"></i>Date de fin
            </label>
            <input type="date" id="end_date" name="end_date"
                   class="form-control form-control-sm"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                   value="<?= ($raw['end_date'] ?? '') ?>">
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm flex-grow-1 fw-semibold">
                <i class="bi bi-search me-1"></i>Rechercher
            </button>
            <?php if ($hasFilters): ?>
                <a href="<?= APP_URL ?>/vehicles" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </div>

    </div>

    <div>
        <button class="btn btn-link btn-sm text-muted p-0 mb-2" type="button"
                data-bs-toggle="collapse" data-bs-target="#advFilters" aria-expanded="false">
            <i class="bi bi-sliders me-1"></i>Filtres avancés
        </button>
        <div class="collapse <?= ($hasBrand || $hasPrice) ? 'show' : '' ?>" id="advFilters">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Marque</label>
                    <input type="text" name="brand" class="form-control form-control-sm"
                           placeholder="ex: Renault"
                           value="<?= ($raw['brand'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Prix max / jour (€)</label>
                    <input type="number" name="max_price" class="form-control form-control-sm"
                           min="1" step="1" placeholder="ex: 80"
                           value="<?= ($raw['max_price'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <?php if ($hasFilters): ?>
        <div class="d-flex flex-wrap gap-1 mt-2 pt-2 border-top">
            <span class="text-muted small me-1">Filtres actifs :</span>
            <?php if ($hasCategory):
                $catName = '';
                foreach ($categories as $c) { if ((int)$c['id'] === (int)$filters['category_id']) $catName = $c['name']; }
            ?>
                <span class="badge bg-warning text-dark"><?= ($catName) ?></span>
            <?php endif; ?>
            <?php if ($hasDates): ?>
                <span class="badge bg-info text-dark">
                    <?= fmt($filters['start_date']) ?> → <?= fmt($filters['end_date']) ?>
                </span>
            <?php endif; ?>
            <?php if ($hasBrand): ?>
                <span class="badge bg-secondary"><?= ($filters['brand']) ?></span>
            <?php endif; ?>
            <?php if ($hasPrice): ?>
                <span class="badge bg-secondary">≤ <?= ($filters['max_price']) ?> €/j</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</form>

<?php if (empty($vehicles)): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-car-front fs-1 d-block mb-3 opacity-25"></i>
        <p class="fs-5">Aucun véhicule disponible<?= $hasDates ? ' sur cette période' : '' ?>.</p>
        <?php if ($hasDates): ?>
            <p class="small">Essayez d'autres dates ou élargissez vos critères.</p>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/vehicles" class="btn btn-outline-dark btn-sm mt-2">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser les filtres
        </a>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($vehicles as $v): ?>
            <div class="col">
                <a href="<?= APP_URL ?>/vehicles/<?= $v['id'] ?>
                    <?php if ($hasDates): ?>?start_date=<?= urlencode($filters['start_date']) ?>&end_date=<?= urlencode($filters['end_date']) ?><?php endif; ?>"
                   class="text-decoration-none text-dark h-100">
                <div class="card h-100 border-0" style="box-shadow: 2px 2px 4px 1px #252525;">

                    <?php if ($v['main_image']): ?>
                        <img src="<?= ($v['main_image']) ?>"
                             class="card-img-top object-fit-cover" style="height:200px"
                             alt="<?= ($v['title']) ?>"
                             loading="lazy" decoding="async">
                    <?php else: ?>
                        <div class="bg-secondary d-flex align-items-center justify-content-center"
                             style="height:200px">
                            <i class="bi bi-car-front text-white" style="font-size:3rem"></i>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasDates): ?>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Disponible
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">

                        <span class="badge bg-warning text-dark mb-2" style="width:fit-content">
                            <?= ($v['category_name']) ?>
                        </span>

                        <h2 class="h6 fw-bold mb-1"><?= ($v['title']) ?></h2>
                        <p class="text-muted small mb-1">
                            <?= ($v['brand']) ?> <?= ($v['model']) ?>
                        </p>

                        <?php if ($hasDates): ?>
                            <div class="alert alert-light border py-1 px-2 mb-2 small">
                                <i class="bi bi-calendar-range me-1 text-muted"></i>
                                <?= fmt($filters['start_date']) ?> → <?= fmt($filters['end_date']) ?>
                                <span class="fw-bold text-dark float-end">
                                    <?= number_format((float)$v['price_per_day'] * $nbDays, 2, ',', ' ') ?> €
                                </span>
                            </div>
                        <?php endif; ?>

                        <p class="fw-bold text-dark mt-auto mb-2">
                            <?= number_format((float)$v['price_per_day'], 2, ',', ' ') ?> € / jour
                        </p>

                        <a href="<?= APP_URL ?>/vehicles/<?= $v['id'] ?>
                            <?php if ($hasDates): ?>?start_date=<?= urlencode($filters['start_date']) ?>&end_date=<?= urlencode($filters['end_date']) ?><?php endif; ?>"
                           class="btn btn-outline-dark btn-sm mt-auto">
                            <?= $hasDates ? 'Réserver ce créneau' : 'Voir le détail' ?>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>

                    </div>

                </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
(function () {
    const startInput = document.getElementById('start_date');
    const endInput   = document.getElementById('end_date');

    startInput.addEventListener('change', function () {
        if (!this.value) return;
        const next = new Date(this.value);
        next.setDate(next.getDate() + 1);
        endInput.min = next.toISOString().split('T')[0];
        if (endInput.value && endInput.value <= this.value) {
            endInput.value = '';
        }
    });
})();
</script>

