<?php declare(strict_types=1);

function ownerDate(string $d): string {
    return (new DateTimeImmutable($d))->format('d/m/Y');
}

function ownerDays(string $start, string $end): int {
    return (int) (new DateTimeImmutable($start))->diff(new DateTimeImmutable($end))->days;
}

function ownerStatusLabel(string $status): string {
    return match ($status) {
        'upcoming' => 'A venir',
        'ongoing' => 'En cours',
        'done' => 'Terminee',
        'cancelled' => 'Annulee',
        default => $status,
    };
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Espace proprietaire</h1>
    <a href="<?= APP_URL ?>/vehicles/create" class="btn btn-dark btn-sm">Ajouter un vehicule</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= ((string) $error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= ((string) $success) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="h4 mb-0"><?= count($vehicles) ?></div><small class="text-muted">Vehicules</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="h4 mb-0"><?= (int) ($stats['nb_upcoming'] ?? 0) ?></div><small class="text-muted">A venir</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="h4 mb-0"><?= (int) ($stats['nb_ongoing'] ?? 0) ?></div><small class="text-muted">En cours</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center bg-dark text-white"><div class="h5 mb-0"><?= number_format((float) ($stats['total_revenue'] ?? 0), 2, ',', ' ') ?> €</div><small class="text-light">Chiffre d'affaires</small></div></div>
</div>

<h2 class="h5">Ma flotte</h2>
<?php if (empty($vehicles)): ?>
    <p class="text-muted">Aucun vehicule pour le moment.</p>
<?php else: ?>
    <div class="table-responsive mb-4">
        <table class="table table-striped align-middle">
            <thead>
                <tr><th>Vehicule</th><th>Categorie</th><th class="text-end">Prix / jour</th><th>Statut</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $v): ?>
                    <tr>
                        <td><strong><?= ($v['title']) ?></strong><br><small class="text-muted"><?= ($v['brand']) ?> <?= ($v['model']) ?> - <?= ($v['registration']) ?></small></td>
                        <td><?= ($v['category_name']) ?></td>
                        <td class="text-end fw-bold"><?= number_format((float) $v['price_per_day'], 2, ',', ' ') ?> €</td>
                        <td><?php if (($v['status'] ?? '') === 'active'): ?><span class="badge bg-success">Actif</span><?php else: ?><span class="badge bg-secondary">Inactif</span><?php endif; ?></td>
                        <td class="text-end"><a href="<?= APP_URL ?>/vehicles/<?= (int) $v['id'] ?>" class="btn btn-sm btn-outline-secondary">Voir</a> <a href="<?= APP_URL ?>/vehicles/<?= (int) $v['id'] ?>/edit" class="btn btn-sm btn-outline-dark">Modifier</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h2 class="h5">Reservations recues</h2>
<?php $allRes = array_merge($resUpcoming ?? [], $resOngoing ?? [], $resPast ?? []); ?>
<?php if (empty($allRes)): ?>
    <p class="text-muted">Aucune reservation recue.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr><th>Client</th><th>Vehicule</th><th>Periode</th><th class="text-center">Statut</th><th class="text-end">Montant</th></tr>
            </thead>
            <tbody>
                <?php foreach ($allRes as $r): ?>
                    <tr>
                        <td><?= ($r['client_firstname']) ?> <?= ($r['client_lastname']) ?></td>
                        <td><?= ($r['vehicle_title']) ?></td>
                        <td><?= ownerDate($r['start_date']) ?> -> <?= ownerDate($r['end_date']) ?><br><small class="text-muted"><?= ownerDays($r['start_date'], $r['end_date']) ?> jour(s)</small></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= (ownerStatusLabel((string) $r['status'])) ?></span></td>
                        <td class="text-end fw-bold"><?= number_format((float) $r['total_price'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

