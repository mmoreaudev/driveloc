<?php declare(strict_types=1); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Gestion des vehicules</h1>
    <a href="<?= APP_URL ?>/dashboard/admin" class="btn btn-outline-dark btn-sm">Retour</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= Security::e((string) $error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= Security::e((string) $success) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr><th>#</th><th>Vehicule</th><th>Proprietaire</th><th class="text-end">Prix / jour</th><th>Statut</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($vehicles as $v): ?>
                <tr>
                    <td><?= (int) $v['id'] ?></td>
                    <td>
                        <strong><?= Security::e($v['title']) ?></strong><br>
                        <small class="text-muted"><?= Security::e($v['brand']) ?> <?= Security::e($v['model']) ?> - <?= Security::e($v['registration']) ?></small>
                    </td>
                    <td><?= Security::e($v['firstname']) ?> <?= Security::e($v['lastname']) ?></td>
                    <td class="text-end fw-bold"><?= number_format((float) $v['price_per_day'], 2, ',', ' ') ?> €</td>
                    <td><?php if (($v['status'] ?? '') === 'active'): ?><span class="badge bg-success">Actif</span><?php else: ?><span class="badge bg-secondary">Inactif</span><?php endif; ?></td>
                    <td class="text-end">
                        <a href="<?= APP_URL ?>/vehicles/<?= (int) $v['id'] ?>" class="btn btn-sm btn-outline-secondary">Voir</a>
                        <form method="POST" action="<?= APP_URL ?>/dashboard/admin/vehicles/<?= (int) $v['id'] ?>/toggle" class="d-inline">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-outline-warning">Activer / Desactiver</button>
                        </form>
                        <form method="POST" action="<?= APP_URL ?>/dashboard/admin/vehicles/<?= (int) $v['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Supprimer ce vehicule ?')">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
