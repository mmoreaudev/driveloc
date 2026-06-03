<?php declare(strict_types=1); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Gestion des utilisateurs</h1>
    <a href="<?= APP_URL ?>/dashboard/admin" class="btn btn-outline-dark btn-sm">w</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= ((string) $error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= ((string) $success) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr><th>#</th><th>Nom</th><th>Email</th><th>Role</th><th>Statut</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int) $u['id'] ?></td>
                    <td><?= ($u['firstname']) ?> <?= ($u['lastname']) ?></td>
                    <td><?= ($u['email']) ?></td>
                    <td><span class="badge bg-secondary"><?= ($u['role']) ?></span></td>
                    <td><?php if (($u['status'] ?? '') === 'active'): ?><span class="badge bg-success">Actif</span><?php else: ?><span class="badge bg-secondary">Inactif</span><?php endif; ?></td>
                    <td class="text-end">
                        <?php if (($u['role'] ?? '') !== 'admin'): ?>
                            <form method="POST" action="<?= APP_URL ?>/dashboard/admin/users/<?= (int) $u['id'] ?>/toggle" class="d-inline">
                                <button type="submit" class="btn btn-sm btn-outline-warning">Activer / Desactiver</button>
                            </form>
                            <form method="POST" action="<?= APP_URL ?>/dashboard/admin/users/<?= (int) $u['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

