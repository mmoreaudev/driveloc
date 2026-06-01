<?php declare(strict_types=1); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="bi bi-people me-2 text-primary"></i>Gestion des utilisateurs
    </h1>
    <a href="<?= APP_URL ?>/dashboard/admin" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Tableau de bord
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-1"></i><?= Security::e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i><?= Security::e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filtre par rôle (JS) -->
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
    <span class="text-muted small me-1">Filtrer :</span>
    <button class="btn btn-sm btn-dark active" data-role-filter="all">
        Tous <span class="badge bg-secondary ms-1"><?= count($users) ?></span>
    </button>
    <?php
        $roleCounts = ['client' => 0, 'owner' => 0, 'admin' => 0];
        foreach ($users as $u) { $roleCounts[$u['role']] = ($roleCounts[$u['role']] ?? 0) + 1; }
        $roleLabels = ['client' => ['Clients', 'bg-primary'], 'owner' => ['Propriétaires', 'bg-warning text-dark'], 'admin' => ['Admins', 'bg-danger']];
    ?>
    <?php foreach ($roleLabels as $role => [$label, $badge]): ?>
        <button class="btn btn-sm btn-outline-secondary" data-role-filter="<?= $role ?>">
            <?= $label ?>
            <span class="badge <?= $badge ?> ms-1"><?= $roleCounts[$role] ?></span>
        </button>
    <?php endforeach; ?>

    <!-- Recherche live -->
    <input type="search" id="userSearch" class="form-control form-control-sm ms-auto"
           style="max-width:220px" placeholder="Rechercher…"
           aria-label="Rechercher un utilisateur">
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="usersTable">
            <thead class="table-dark">
                <tr>
                    <th class="ps-3">#</th>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th class="text-center">Rôle</th>
                    <th class="text-center">Statut</th>
                    <th>Inscrit le</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $roleBadge = [
                    'admin'  => 'bg-danger',
                    'owner'  => 'bg-warning text-dark',
                    'client' => 'bg-primary',
                ];
                foreach ($users as $u):
                    $initials = mb_strtoupper(mb_substr($u['firstname'], 0, 1))
                              . mb_strtoupper(mb_substr($u['lastname'],  0, 1));
                    $avatarBg = match($u['role']) {
                        'admin'  => 'bg-danger',
                        'owner'  => 'bg-warning text-dark',
                        default  => 'bg-primary',
                    };
                ?>
                    <tr data-role="<?= Security::e($u['role']) ?>"
                        data-search="<?= Security::e(strtolower($u['firstname'].' '.$u['lastname'].' '.$u['email'])) ?>">

                        <td class="ps-3 text-muted small"><?= (int)$u['id'] ?></td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center
                                            justify-content-center flex-shrink-0 <?= $avatarBg ?>"
                                     style="width:34px;height:34px;font-size:.75rem;font-weight:700">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <div class="fw-semibold small">
                                        <?= Security::e($u['firstname']) ?>
                                        <?= Security::e($u['lastname']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="small text-muted"><?= Security::e($u['email']) ?></td>

                        <td class="text-center">
                            <span class="badge <?= $roleBadge[$u['role']] ?? 'bg-secondary' ?>">
                                <?= Security::e($u['role']) ?>
                            </span>
                        </td>

                        <td class="text-center">
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Actif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-dash-circle me-1"></i>Inactif
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="small text-muted text-nowrap">
                            <?= Security::e(date('d/m/Y', strtotime($u['created_at']))) ?>
                        </td>

                        <td class="text-end pe-3">
                            <?php if ($u['role'] !== 'admin'): ?>

                                <!-- Activer / Désactiver -->
                                <form method="POST"
                                      action="<?= APP_URL ?>/dashboard/admin/users/<?= (int)$u['id'] ?>/toggle"
                                      class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <button type="submit"
                                            class="btn btn-sm me-1 <?= $u['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                            title="<?= $u['status'] === 'active' ? 'Désactiver' : 'Activer' ?>">
                                        <i class="bi bi-<?= $u['status'] === 'active' ? 'pause-circle' : 'play-circle' ?>"></i>
                                        <?= $u['status'] === 'active' ? 'Désactiver' : 'Activer' ?>
                                    </button>
                                </form>

                                <!-- Supprimer -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteUserModal"
                                        data-user-id="<?= (int)$u['id'] ?>"
                                        data-user-name="<?= Security::e($u['firstname'] . ' ' . $u['lastname']) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>

                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal suppression utilisateur -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <p class="mb-1">Supprimer définitivement le compte de :</p>
                <strong id="deleteUserName" class="d-block mb-3 text-danger"></strong>
                <p class="text-muted small mb-0">Toutes ses données seront perdues.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form id="deleteUserForm" method="POST" action="">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Modal suppression
document.getElementById('deleteUserModal').addEventListener('show.bs.modal', function (e) {
    const btn  = e.relatedTarget;
    document.getElementById('deleteUserName').textContent = btn.getAttribute('data-user-name');
    document.getElementById('deleteUserForm').action =
        '<?= APP_URL ?>/dashboard/admin/users/' + btn.getAttribute('data-user-id') + '/delete';
});

// Filtre par rôle
document.querySelectorAll('[data-role-filter]').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('[data-role-filter]').forEach(b => b.classList.remove('active', 'btn-dark'));
        this.classList.add('active', 'btn-dark');
        this.classList.remove('btn-outline-secondary');
        const role = this.getAttribute('data-role-filter');
        filterTable();
    });
});

// Recherche live
document.getElementById('userSearch').addEventListener('input', filterTable);

function filterTable() {
    const role  = (document.querySelector('[data-role-filter].active') || {}).getAttribute('data-role-filter') || 'all';
    const query = document.getElementById('userSearch').value.toLowerCase().trim();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        const matchRole   = role === 'all' || row.getAttribute('data-role') === role;
        const matchSearch = !query || (row.getAttribute('data-search') || '').includes(query);
        row.style.display = (matchRole && matchSearch) ? '' : 'none';
    });
}
</script>

