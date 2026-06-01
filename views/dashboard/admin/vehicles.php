<?php declare(strict_types=1); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="bi bi-car-front me-2 text-warning"></i>Gestion des véhicules
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

<!-- Filtre statut + recherche live -->
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
    <span class="text-muted small me-1">Filtrer :</span>
    <button class="btn btn-sm btn-dark active" data-status-filter="all">
        Tous <span class="badge bg-secondary ms-1"><?= count($vehicles) ?></span>
    </button>
    <?php
        $nbActive   = count(array_filter($vehicles, fn($v) => $v['status'] === 'active'));
        $nbInactive = count($vehicles) - $nbActive;
    ?>
    <button class="btn btn-sm btn-outline-secondary" data-status-filter="active">
        Actifs <span class="badge bg-success ms-1"><?= $nbActive ?></span>
    </button>
    <button class="btn btn-sm btn-outline-secondary" data-status-filter="inactive">
        Inactifs <span class="badge bg-secondary ms-1"><?= $nbInactive ?></span>
    </button>

    <input type="search" id="vehicleSearch" class="form-control form-control-sm ms-auto"
           style="max-width:220px" placeholder="Titre, marque, propriétaire…"
           aria-label="Rechercher un véhicule">
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="vehiclesTable">
            <thead class="table-dark">
                <tr>
                    <th class="ps-3">Photo</th>
                    <th>Véhicule</th>
                    <th>Immat.</th>
                    <th>Catégorie</th>
                    <th>Propriétaire</th>
                    <th class="text-end">€ / jour</th>
                    <th class="text-center">Statut</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $v):
                    $searchText = strtolower(
                        $v['title'].' '.$v['brand'].' '.$v['model'].' '
                        .$v['registration'].' '.$v['firstname'].' '.$v['lastname']
                    );
                ?>
                    <tr data-status="<?= Security::e($v['status']) ?>"
                        data-search="<?= Security::e($searchText) ?>">

                        <td class="ps-3">
                            <?php if ($v['main_image']): ?>
                                <img src="<?= UPLOAD_URL . Security::e($v['main_image']) ?>"
                                     class="rounded object-fit-cover"
                                     style="width:70px;height:48px" alt="">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="width:70px;height:48px">
                                    <i class="bi bi-car-front text-secondary"></i>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <strong class="small"><?= Security::e($v['title']) ?></strong><br>
                            <span class="text-muted" style="font-size:.75rem">
                                <?= Security::e($v['brand']) ?> <?= Security::e($v['model']) ?>
                            </span>
                        </td>

                        <td>
                            <code class="small"><?= Security::e($v['registration']) ?></code>
                        </td>

                        <td class="small"><?= Security::e($v['category_name']) ?></td>

                        <td class="small">
                            <?= Security::e($v['firstname']) ?> <?= Security::e($v['lastname']) ?>
                        </td>

                        <td class="text-end fw-bold text-nowrap small">
                            <?= number_format((float)$v['price_per_day'], 2, ',', ' ') ?> €
                        </td>

                        <td class="text-center">
                            <?php if ($v['status'] === 'active'): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactif</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-end pe-3 text-nowrap">
                            <!-- Voir -->
                            <a href="<?= APP_URL ?>/vehicles/<?= (int)$v['id'] ?>"
                               class="btn btn-sm btn-outline-secondary me-1" title="Voir l'annonce">
                                <i class="bi bi-eye"></i>
                            </a>
                            <!-- Activer / Désactiver -->
                            <form method="POST"
                                  action="<?= APP_URL ?>/dashboard/admin/vehicles/<?= (int)$v['id'] ?>/toggle"
                                  class="d-inline">
                                <?= Security::csrfField() ?>
                                <button type="submit"
                                        class="btn btn-sm me-1 <?= $v['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                        title="<?= $v['status'] === 'active' ? 'Désactiver' : 'Activer' ?>">
                                    <i class="bi <?= $v['status'] === 'active' ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                </button>
                            </form>
                            <!-- Supprimer -->
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteVehicleModal"
                                    data-vehicle-id="<?= (int)$v['id'] ?>"
                                    data-vehicle-title="<?= Security::e($v['title']) ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal suppression véhicule -->
<div class="modal fade" id="deleteVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Supprimer le véhicule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <p class="mb-1">Supprimer définitivement :</p>
                <strong id="deleteVehicleTitle" class="d-block mb-3 text-danger"></strong>
                <p class="text-muted small mb-0">
                    L'annonce et la photo seront supprimées.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form id="deleteVehicleForm" method="POST" action="">
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
document.getElementById('deleteVehicleModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('deleteVehicleTitle').textContent = btn.getAttribute('data-vehicle-title');
    document.getElementById('deleteVehicleForm').action =
        '<?= APP_URL ?>/dashboard/admin/vehicles/' + btn.getAttribute('data-vehicle-id') + '/delete';
});

// Filtre statut
document.querySelectorAll('[data-status-filter]').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('[data-status-filter]').forEach(b => {
            b.classList.remove('active', 'btn-dark');
            b.classList.add('btn-outline-secondary');
        });
        this.classList.add('active', 'btn-dark');
        this.classList.remove('btn-outline-secondary');
        filterVehicles();
    });
});

// Recherche live
document.getElementById('vehicleSearch').addEventListener('input', filterVehicles);

function filterVehicles() {
    const status = (document.querySelector('[data-status-filter].active') || {}).getAttribute('data-status-filter') || 'all';
    const query  = document.getElementById('vehicleSearch').value.toLowerCase().trim();
    document.querySelectorAll('#vehiclesTable tbody tr').forEach(row => {
        const matchStatus = status === 'all' || row.getAttribute('data-status') === status;
        const matchSearch = !query || (row.getAttribute('data-search') || '').includes(query);
        row.style.display = (matchStatus && matchSearch) ? '' : 'none';
    });
}
</script>


<?php if ($error): ?>
    <div class="alert alert-danger"><?= Security::e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= Security::e($success) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Véhicule</th>
                <th>Catégorie</th>
                <th>Propriétaire</th>
                <th>Prix / jour</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehicles as $v): ?>
                <tr>
                    <td>
                        <strong><?= Security::e($v['title']) ?></strong><br>
                        <small class="text-muted"><?= Security::e($v['brand']) ?> <?= Security::e($v['model']) ?></small>
                    </td>
                    <td><?= Security::e($v['category_name']) ?></td>
                    <td class="small"><?= Security::e($v['firstname'] . ' ' . $v['lastname']) ?></td>
                    <td><?= number_format((float)$v['price_per_day'], 2, ',', ' ') ?> €</td>
                    <td>
                        <?php if ($v['status'] === 'active'): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Activer / Désactiver -->
                        <form method="POST"
                              action="<?= APP_URL ?>/dashboard/admin/vehicles/<?= $v['id'] ?>/toggle"
                              class="d-inline">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary me-1"
                                    title="<?= $v['status'] === 'active' ? 'Désactiver' : 'Activer' ?>">
                                <i class="bi bi-<?= $v['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i>
                            </button>
                        </form>
                        <!-- Modifier (délégation) -->
                        <a href="<?= APP_URL ?>/vehicles/<?= $v['id'] ?>/edit"
                           class="btn btn-sm btn-outline-dark me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <!-- Supprimer -->
                        <form method="POST"
                              action="<?= APP_URL ?>/dashboard/admin/vehicles/<?= $v['id'] ?>/delete"
                              class="d-inline"
                              onsubmit="return confirm('Supprimer ce véhicule définitivement ?')">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
