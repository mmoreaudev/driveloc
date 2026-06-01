<?php declare(strict_types=1);

$badgeMap = [
    'upcoming'  => ['bg-primary',   'À venir'],
    'ongoing'   => ['bg-success',   'En cours'],
    'done'      => ['bg-dark',      'Terminée'],
    'cancelled' => ['bg-secondary', 'Annulée'],
];
?>

<!-- ═══════ EN-TÊTE ════════════════════════════════════ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="bi bi-speedometer2 me-2 text-warning"></i>Tableau de bord — Administration
    </h1>
    <span class="badge bg-danger fs-6">Admin</span>
</div>

<?php if ($error ?? null): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-1"></i><?= Security::e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($success ?? null): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i><?= Security::e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ═══════ KPI — LIGNE 1 : Chiffre d'affaires ════════ -->
<div class="row g-3 mb-3">

    <div class="col-lg-6">
        <div class="card border-0 bg-dark text-white h-100 p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="small text-secondary mb-1">
                        <i class="bi bi-graph-up me-1"></i>Chiffre d'affaires théorique
                        <span class="ms-1 text-muted" title="Toutes réservations hors annulées"
                              data-bs-toggle="tooltip">
                            <i class="bi bi-info-circle"></i>
                        </span>
                    </div>
                    <div class="display-6 fw-bold text-warning">
                        <?= number_format((float)$resStats['revenue_theoretical'], 2, ',', ' ') ?> €
                    </div>
                </div>
                <i class="bi bi-currency-euro fs-1 opacity-25"></i>
            </div>
            <hr class="border-secondary my-2">
            <div class="d-flex gap-4 small">
                <div>
                    <span class="text-secondary">Confirmé (terminées)</span><br>
                    <span class="fw-bold text-success">
                        <?= number_format((float)$resStats['revenue_confirmed'], 2, ',', ' ') ?> €
                    </span>
                </div>
                <div>
                    <span class="text-secondary">En attente</span><br>
                    <span class="fw-bold text-warning">
                        <?= number_format(
                            (float)$resStats['revenue_theoretical'] - (float)$resStats['revenue_confirmed'],
                            2, ',', ' '
                        ) ?> €
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="row g-3 h-100">

            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="fs-2 fw-bold text-primary"><?= (int)$resStats['nb_upcoming'] ?></div>
                    <div class="small text-muted">Réservations à venir</div>
                </div>
            </div>

            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3 h-100 border-start border-success border-3">
                    <div class="fs-2 fw-bold text-success"><?= (int)$resStats['nb_ongoing'] ?></div>
                    <div class="small text-muted">En cours</div>
                </div>
            </div>

            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="fs-2 fw-bold text-dark"><?= (int)$resStats['nb_done'] ?></div>
                    <div class="small text-muted">Terminées</div>
                </div>
            </div>

            <div class="col-6">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="fs-2 fw-bold text-secondary"><?= (int)$resStats['nb_cancelled'] ?></div>
                    <div class="small text-muted">Annulées</div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- ═══════ KPI — LIGNE 2 : Utilisateurs & Véhicules ═══ -->
<div class="row g-3 mb-4">

    <!-- Utilisateurs -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-people me-2 text-primary"></i>Utilisateurs
                    </h2>
                    <a href="<?= APP_URL ?>/dashboard/admin/users"
                       class="btn btn-outline-primary btn-sm">Gérer</a>
                </div>
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <div class="fs-3 fw-bold"><?= (int)$userStats['total'] ?></div>
                        <div class="small text-muted">Total</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="fs-4 fw-bold text-primary"><?= (int)$userStats['nb_clients'] ?></div>
                        <div class="small text-muted">Clients</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="fs-4 fw-bold text-warning"><?= (int)$userStats['nb_owners'] ?></div>
                        <div class="small text-muted">Propriétaires</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="fs-4 fw-bold text-success"><?= (int)$userStats['nb_active'] ?></div>
                        <div class="small text-muted">Actifs</div>
                    </div>
                    <?php if ((int)$userStats['nb_inactive'] > 0): ?>
                    <div class="vr"></div>
                    <div>
                        <div class="fs-4 fw-bold text-danger"><?= (int)$userStats['nb_inactive'] ?></div>
                        <div class="small text-muted">Inactifs</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Véhicules -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-car-front me-2 text-warning"></i>Véhicules
                    </h2>
                    <a href="<?= APP_URL ?>/dashboard/admin/vehicles"
                       class="btn btn-outline-warning btn-sm">Gérer</a>
                </div>
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <div class="fs-3 fw-bold"><?= (int)$vehicleStats['total'] ?></div>
                        <div class="small text-muted">Total</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="fs-4 fw-bold text-success"><?= (int)$vehicleStats['nb_active'] ?></div>
                        <div class="small text-muted">Actifs</div>
                    </div>
                    <div class="vr"></div>
                    <div>
                        <div class="fs-4 fw-bold text-secondary"><?= (int)$vehicleStats['nb_inactive'] ?></div>
                        <div class="small text-muted">Inactifs</div>
                    </div>
                </div>
                <!-- Barre de remplissage active/inactive -->
                <?php
                    $total = max(1, (int)$vehicleStats['total']);
                    $pct   = min(100, (int)round((int)$vehicleStats['nb_active'] / $total * 100));
                ?>
                <div class="mt-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Taux de publication active</span>
                        <span><?= $pct ?>%</span>
                    </div>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ═══════ RACCOURCIS ════════════════════════════════ -->
<h2 class="h6 fw-semibold text-uppercase text-muted mb-3 letter-spacing-1">
    Actions rapides
</h2>
<div class="row g-3 mb-4">

    <div class="col-md-4">
        <a href="<?= APP_URL ?>/dashboard/admin/users"
           class="card border-0 shadow-sm text-decoration-none text-dark p-3 d-flex flex-row align-items-center gap-3 h-100">
            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                <i class="bi bi-people fs-4 text-primary"></i>
            </div>
            <div>
                <div class="fw-semibold">Utilisateurs</div>
                <small class="text-muted">Activer · Désactiver · Supprimer</small>
            </div>
            <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= APP_URL ?>/dashboard/admin/vehicles"
           class="card border-0 shadow-sm text-decoration-none text-dark p-3 d-flex flex-row align-items-center gap-3 h-100">
            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                <i class="bi bi-car-front fs-4 text-warning"></i>
            </div>
            <div>
                <div class="fw-semibold">Véhicules</div>
                <small class="text-muted">Désactiver · Supprimer</small>
            </div>
            <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= APP_URL ?>/dashboard/admin/categories"
           class="card border-0 shadow-sm text-decoration-none text-dark p-3 d-flex flex-row align-items-center gap-3 h-100">
            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                <i class="bi bi-tags fs-4 text-success"></i>
            </div>
            <div>
                <div class="fw-semibold">Catégories</div>
                <small class="text-muted">Ajouter · Gérer</small>
            </div>
            <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </a>
    </div>

</div>

<!-- ═══════ ACTIVITÉ RÉCENTE ══════════════════════════ -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 fw-semibold text-uppercase text-muted mb-0">
        Dernières réservations
    </h2>
</div>

<?php if (empty($recentRes)): ?>
    <p class="text-muted small">Aucune réservation enregistrée.</p>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Période</th>
                        <th class="text-end">Montant</th>
                        <th class="text-center pe-3">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentRes as $r):
                        [$bg, $label] = $badgeMap[$r['status']] ?? ['bg-secondary', $r['status']];
                        $start = (new DateTimeImmutable($r['start_date']))->format('d/m/Y');
                        $end   = (new DateTimeImmutable($r['end_date']))->format('d/m/Y');
                    ?>
                        <tr>
                            <td class="ps-3 text-muted small"><?= (int)$r['id'] ?></td>
                            <td>
                                <span class="fw-semibold small">
                                    <?= Security::e($r['client_firstname']) ?>
                                    <?= Security::e($r['client_lastname']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($r['vehicle_image']): ?>
                                        <img src="<?= UPLOAD_URL . Security::e($r['vehicle_image']) ?>"
                                             class="rounded object-fit-cover flex-shrink-0"
                                             style="width:40px;height:30px" alt="">
                                    <?php endif; ?>
                                    <span class="small">
                                        <?= Security::e($r['vehicle_title']) ?>
                                        <span class="text-muted">
                                            (<?= Security::e($r['vehicle_brand']) ?>
                                             <?= Security::e($r['vehicle_model']) ?>)
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td class="small text-nowrap">
                                <?= $start ?> → <?= $end ?>
                            </td>
                            <td class="text-end fw-bold text-nowrap small">
                                <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                            </td>
                            <td class="text-center pe-3">
                                <span class="badge <?= $bg ?>"><?= $label ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
