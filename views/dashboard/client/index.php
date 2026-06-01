<?php declare(strict_types=1);

function clientDate(string $d): string {
    return (new DateTimeImmutable($d))->format('d/m/Y');
}

function clientDays(string $start, string $end): int {
    return (int) (new DateTimeImmutable($start))->diff(new DateTimeImmutable($end))->days;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Mon espace client</h1>
    <a href="<?= APP_URL ?>/vehicles" class="btn btn-warning btn-sm">Nouvelle reservation</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= Security::e((string) $error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= Security::e((string) $success) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="h4 mb-0"><?= (int) ($stats['nb_upcoming'] ?? 0) ?></div>
            <small class="text-muted">A venir</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="h4 mb-0"><?= (int) ($stats['nb_ongoing'] ?? 0) ?></div>
            <small class="text-muted">En cours</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="h4 mb-0"><?= (int) ($stats['nb_done'] ?? 0) ?></div>
            <small class="text-muted">Terminees</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-3 text-center bg-dark text-white">
            <div class="h4 mb-0"><?= number_format((float) ($stats['total_spent'] ?? 0), 2, ',', ' ') ?> €</div>
            <small class="text-light">Total depense</small>
        </div>
    </div>
</div>

<h2 class="h5">Reservations a venir</h2>
<?php if (empty($upcoming)): ?>
    <p class="text-muted">Aucune reservation a venir.</p>
<?php else: ?>
    <div class="table-responsive mb-4">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Vehicule</th>
                    <th>Periode</th>
                    <th class="text-end">Prix</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming as $r): ?>
                    <tr>
                        <td>
                            <strong><?= Security::e($r['title']) ?></strong><br>
                            <small class="text-muted"><?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?></small>
                        </td>
                        <td>
                            <?= clientDate($r['start_date']) ?> -> <?= clientDate($r['end_date']) ?><br>
                            <small class="text-muted"><?= clientDays($r['start_date'], $r['end_date']) ?> jour(s)</small>
                        </td>
                        <td class="text-end fw-bold"><?= number_format((float) $r['total_price'], 2, ',', ' ') ?> €</td>
                        <td class="text-end">
                            <form method="POST" action="<?= APP_URL ?>/reservations/<?= (int) $r['id'] ?>/cancel" onsubmit="return confirm('Annuler cette reservation ?')">
                                <?= Security::csrfField() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm">Annuler</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h2 class="h5">Reservations en cours</h2>
<?php if (empty($ongoing)): ?>
    <p class="text-muted">Aucune reservation en cours.</p>
<?php else: ?>
    <div class="table-responsive mb-4">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Vehicule</th>
                    <th>Periode</th>
                    <th class="text-end">Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ongoing as $r): ?>
                    <tr>
                        <td>
                            <strong><?= Security::e($r['title']) ?></strong><br>
                            <small class="text-muted"><?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?></small>
                        </td>
                        <td><?= clientDate($r['start_date']) ?> -> <?= clientDate($r['end_date']) ?></td>
                        <td class="text-end fw-bold"><?= number_format((float) $r['total_price'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h2 class="h5">Historique</h2>
<?php if (empty($past)): ?>
    <p class="text-muted">Aucune reservation passee.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Vehicule</th>
                    <th>Periode</th>
                    <th class="text-center">Statut</th>
                    <th class="text-end">Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($past as $r): ?>
                    <tr>
                        <td><?= Security::e($r['title']) ?></td>
                        <td><?= clientDate($r['start_date']) ?> -> <?= clientDate($r['end_date']) ?></td>
                        <td class="text-center">
                            <?php if (($r['status'] ?? '') === 'cancelled'): ?>
                                <span class="badge bg-secondary">Annulee</span>
                            <?php else: ?>
                                <span class="badge bg-dark">Terminee</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-bold"><?= number_format((float) $r['total_price'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
