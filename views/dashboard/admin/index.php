<?php declare(strict_types=1); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Administration</h1>
    <span class="badge bg-danger">Admin</span>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= ((string) $error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= ((string) $success) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="h4 mb-0"><?= (int) ($resStats['total_reservations'] ?? 0) ?></div><small class="text-muted">Reservations</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="h4 mb-0"><?= (int) ($userStats['total'] ?? 0) ?></div><small class="text-muted">Utilisateurs</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="h4 mb-0"><?= (int) ($vehicleStats['total'] ?? 0) ?></div><small class="text-muted">Vehicules</small></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center bg-dark text-white"><div class="h5 mb-0"><?= number_format((float) ($resStats['revenue_theoretical'] ?? 0), 2, ',', ' ') ?> €</div><small class="text-light">CA theorique</small></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><a class="btn btn-outline-dark w-100" href="<?= APP_URL ?>/dashboard/admin/users">Gerer les utilisateurs</a></div>
    <div class="col-md-4"><a class="btn btn-outline-dark w-100" href="<?= APP_URL ?>/dashboard/admin/vehicles">Gerer les vehicules</a></div>
    <div class="col-md-4"><a class="btn btn-outline-dark w-100" href="<?= APP_URL ?>/dashboard/admin/categories">Gerer les categories</a></div>
</div>

<h2 class="h5">Dernieres reservations</h2>
<?php if (empty($recentRes)): ?>
    <p class="text-muted">Aucune reservation.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>#</th><th>Client</th><th>Vehicule</th><th>Periode</th><th class="text-end">Montant</th><th class="text-center">Statut</th></tr></thead>
            <tbody>
                <?php foreach ($recentRes as $r): ?>
                    <tr>
                        <td><?= (int) $r['id'] ?></td>
                        <td><?= ($r['client_firstname']) ?> <?= ($r['client_lastname']) ?></td>
                        <td><?= ($r['vehicle_title']) ?></td>
                        <td><?= ((new DateTimeImmutable($r['start_date']))->format('d/m/Y')) ?> -> <?= ((new DateTimeImmutable($r['end_date']))->format('d/m/Y')) ?></td>
                        <td class="text-end fw-bold"><?= number_format((float) $r['total_price'], 2, ',', ' ') ?> €</td>
                        <td class="text-center"><span class="badge bg-secondary"><?= ($r['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

