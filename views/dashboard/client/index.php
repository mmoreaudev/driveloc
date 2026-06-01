<?php declare(strict_types=1);

// ── Helpers locaux ────────────────────────────────────
function fmtDate(string $d): string {
    return (new DateTimeImmutable($d))->format('d/m/Y');
}
function nbJours(string $start, string $end): int {
    return (int) (new DateTimeImmutable($start))->diff(new DateTimeImmutable($end))->days;
}
?>

<!-- ═══════ EN-TÊTE ════════════════════════════════════ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="bi bi-calendar-check me-2 text-warning"></i>Mes réservations
    </h1>
    <a href="<?= APP_URL ?>/vehicles" class="btn btn-warning btn-sm fw-semibold">
        <i class="bi bi-plus-lg me-1"></i>Nouvelle réservation
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

<!-- ═══════ STATISTIQUES ══════════════════════════════ -->
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-primary"><?= (int)$stats['nb_upcoming'] ?></div>
            <div class="small text-muted">À venir</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-success"><?= (int)$stats['nb_ongoing'] ?></div>
            <div class="small text-muted">En cours</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-dark"><?= (int)$stats['nb_done'] ?></div>
            <div class="small text-muted">Terminées</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 bg-dark text-white text-center py-3">
            <div class="fs-3 fw-bold text-warning">
                <?= number_format((float)$stats['total_spent'], 2, ',', ' ') ?> €
            </div>
            <div class="small text-secondary">Total dépensé</div>
        </div>
    </div>

</div>

<!-- ═══════ ONGLETS ═══════════════════════════════════ -->
<ul class="nav nav-tabs mb-0" id="reservationTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="tab-upcoming"
                data-bs-toggle="tab" data-bs-target="#upcoming"
                type="button" role="tab">
            <i class="bi bi-clock me-1"></i>À venir
            <?php if ((int)$stats['nb_upcoming'] > 0): ?>
                <span class="badge bg-primary ms-1"><?= (int)$stats['nb_upcoming'] ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="tab-ongoing"
                data-bs-toggle="tab" data-bs-target="#ongoing"
                type="button" role="tab">
            <i class="bi bi-play-circle me-1"></i>En cours
            <?php if ((int)$stats['nb_ongoing'] > 0): ?>
                <span class="badge bg-success ms-1"><?= (int)$stats['nb_ongoing'] ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="tab-past"
                data-bs-toggle="tab" data-bs-target="#past"
                type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i>Historique
            <?php if ((int)$stats['nb_done'] + (int)$stats['nb_cancelled'] > 0): ?>
                <span class="badge bg-secondary ms-1">
                    <?= (int)$stats['nb_done'] + (int)$stats['nb_cancelled'] ?>
                </span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-4 bg-white shadow-sm mb-4">

    <!-- ══ À VENIR ══════════════════════════════════════ -->
    <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
        <?php if (empty($upcoming)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-calendar-plus fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-3">Vous n'avez aucune réservation à venir.</p>
                <a href="<?= APP_URL ?>/vehicles" class="btn btn-warning fw-semibold">
                    <i class="bi bi-search me-1"></i>Trouver un véhicule
                </a>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($upcoming as $r):
                    $jours = nbJours($r['start_date'], $r['end_date']);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">

                            <!-- Photo -->
                            <?php if ($r['main_image']): ?>
                                <img src="<?= UPLOAD_URL . Security::e($r['main_image']) ?>"
                                     class="card-img-top object-fit-cover" style="height:150px"
                                     alt="<?= Security::e($r['title']) ?>">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center"
                                     style="height:150px">
                                    <i class="bi bi-car-front text-secondary" style="font-size:2.5rem"></i>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary">À venir</span>
                                    <span class="fw-bold text-dark">
                                        <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                                    </span>
                                </div>

                                <h2 class="h6 fw-bold mb-1"><?= Security::e($r['title']) ?></h2>
                                <p class="small text-muted mb-2">
                                    <?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?>
                                </p>

                                <!-- Période -->
                                <div class="bg-light rounded px-3 py-2 mb-3 small">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="text-muted">Début</div>
                                            <div class="fw-semibold"><?= fmtDate($r['start_date']) ?></div>
                                        </div>
                                        <div class="text-center text-muted">
                                            <i class="bi bi-arrow-right d-block mt-3"></i>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted">Fin</div>
                                            <div class="fw-semibold"><?= fmtDate($r['end_date']) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-center text-muted mt-1">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= $jours ?> jour<?= $jours > 1 ? 's' : '' ?>
                                        — <?= number_format((float)$r['total_price'] / max(1, $jours), 2, ',', ' ') ?> €/jour
                                    </div>
                                </div>

                                <!-- Bouton annulation -->
                                <div class="mt-auto">
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cancelModal"
                                            data-reservation-id="<?= $r['id'] ?>"
                                            data-vehicle-title="<?= Security::e($r['title']) ?>">
                                        <i class="bi bi-x-circle me-1"></i>Annuler la réservation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ══ EN COURS ═════════════════════════════════════ -->
    <div class="tab-pane fade" id="ongoing" role="tabpanel">
        <?php if (empty($ongoing)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-hourglass-split fs-1 d-block mb-2 opacity-25"></i>
                Aucun véhicule en location actuellement.
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($ongoing as $r):
                    $jours       = nbJours($r['start_date'], $r['end_date']);
                    $joursEcoules = (int) (new DateTimeImmutable('today'))
                                        ->diff(new DateTimeImmutable($r['start_date']))->days;
                    $joursRestants = max(0, $jours - $joursEcoules);
                    $progression   = $jours > 0 ? min(100, (int) round($joursEcoules / $jours * 100)) : 0;
                ?>
                    <div class="col-md-6">
                        <div class="card border-success border-2 shadow-sm h-100">

                            <?php if ($r['main_image']): ?>
                                <img src="<?= UPLOAD_URL . Security::e($r['main_image']) ?>"
                                     class="card-img-top object-fit-cover" style="height:150px"
                                     alt="<?= Security::e($r['title']) ?>">
                            <?php endif; ?>

                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-success">
                                        <i class="bi bi-play-fill me-1"></i>En cours
                                    </span>
                                    <strong><?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €</strong>
                                </div>

                                <h2 class="h6 fw-bold mb-1"><?= Security::e($r['title']) ?></h2>
                                <p class="small text-muted mb-2">
                                    <?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?>
                                </p>

                                <p class="small mb-1">
                                    <i class="bi bi-calendar-range me-1 text-muted"></i>
                                    <?= fmtDate($r['start_date']) ?> → <?= fmtDate($r['end_date']) ?>
                                </p>

                                <!-- Barre de progression -->
                                <div class="mb-1 mt-2">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Progression</span>
                                        <span><?= $joursRestants ?> jour<?= $joursRestants > 1 ? 's' : '' ?> restant<?= $joursRestants > 1 ? 's' : '' ?></span>
                                    </div>
                                    <div class="progress" style="height:6px">
                                        <div class="progress-bar bg-success"
                                             role="progressbar"
                                             style="width:<?= $progression ?>%"
                                             aria-valuenow="<?= $progression ?>"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ══ HISTORIQUE ═══════════════════════════════════ -->
    <div class="tab-pane fade" id="past" role="tabpanel">
        <?php if (empty($past)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
                Aucune réservation passée.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Véhicule</th>
                            <th>Période</th>
                            <th class="text-center">Durée</th>
                            <th class="text-end">Prix total</th>
                            <th class="text-center">Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past as $r):
                            $jours = nbJours($r['start_date'], $r['end_date']);
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($r['main_image']): ?>
                                            <img src="<?= UPLOAD_URL . Security::e($r['main_image']) ?>"
                                                 class="rounded object-fit-cover flex-shrink-0"
                                                 style="width:48px;height:36px" alt="">
                                        <?php endif; ?>
                                        <div>
                                            <strong class="small"><?= Security::e($r['title']) ?></strong><br>
                                            <span class="text-muted" style="font-size:.75rem">
                                                <?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="small text-nowrap">
                                    <?= fmtDate($r['start_date']) ?><br>
                                    <span class="text-muted">→ <?= fmtDate($r['end_date']) ?></span>
                                </td>
                                <td class="text-center small text-muted text-nowrap">
                                    <?= $jours ?> j.
                                </td>
                                <td class="text-end fw-bold text-nowrap">
                                    <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                                </td>
                                <td class="text-center">
                                    <?php if ($r['status'] === 'cancelled'): ?>
                                        <span class="badge bg-secondary">Annulée</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark">Terminée</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'done'): ?>
                                        <a href="<?= APP_URL ?>/vehicles/<?= (int)$r['vehicle_id'] ?>"
                                           class="btn btn-outline-warning btn-sm text-nowrap"
                                           title="Réserver à nouveau">
                                            <i class="bi bi-arrow-repeat me-1"></i>Re-réserver
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ═══════ MODAL CONFIRMATION ANNULATION ════════════ -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmer l'annulation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <p class="mb-1">Annuler la réservation pour :</p>
                <strong id="cancelVehicleTitle" class="d-block mb-3"></strong>
                <p class="text-muted small mb-0">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Non, garder
                </button>
                <form id="cancelForm" method="POST" action="">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="bi bi-x-circle me-1"></i>Oui, annuler
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Injection dynamique de l'ID de réservation dans le modal d'annulation
document.getElementById('cancelModal').addEventListener('show.bs.modal', function (e) {
    const btn   = e.relatedTarget;
    const id    = btn.getAttribute('data-reservation-id');
    const title = btn.getAttribute('data-vehicle-title');
    document.getElementById('cancelVehicleTitle').textContent = title;
    document.getElementById('cancelForm').action =
        '<?= APP_URL ?>/reservations/' + id + '/cancel';
});
</script>


<h1 class="h4 fw-bold mb-4">
    <i class="bi bi-calendar-check me-2 text-warning"></i>Mes réservations
</h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= Security::e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= Security::e($success) ?></div>
<?php endif; ?>

<!-- Onglets -->
<ul class="nav nav-tabs mb-4" id="reservationTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#upcoming">
            À venir <span class="badge bg-primary ms-1"><?= count($upcoming) ?></span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ongoing">
            En cours <span class="badge bg-success ms-1"><?= count($ongoing) ?></span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#past">
            Historique <span class="badge bg-secondary ms-1"><?= count($past) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- À VENIR -->
    <div class="tab-pane fade show active" id="upcoming">
        <?php if (empty($upcoming)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-calendar fs-1 d-block mb-2 opacity-25"></i>
                Aucune réservation à venir.
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($upcoming as $r):
                    $jours = nbJours($r['start_date'], $r['end_date']);
                ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <?php if ($r['main_image']): ?>
                                <img src="<?= UPLOAD_URL . Security::e($r['main_image']) ?>"
                                     class="card-img-top object-fit-cover" style="height:140px" alt="">
                            <?php endif; ?>
                            <div class="card-body">
                                <span class="badge bg-primary mb-2">À venir</span>
                                <h2 class="h6 fw-bold"><?= Security::e($r['title']) ?></h2>
                                <p class="small text-muted mb-2">
                                    <?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?>
                                </p>
                                <p class="small mb-1">
                                    <i class="bi bi-calendar-event me-1 text-muted"></i>
                                    Du <strong><?= fmtDate($r['start_date']) ?></strong>
                                    au <strong><?= fmtDate($r['end_date']) ?></strong>
                                    <span class="text-muted">(<?= $jours ?> jour<?= $jours > 1 ? 's' : '' ?>)</span>
                                </p>
                                <!-- Détail du calcul -->
                                <p class="small text-muted mb-1">
                                    <i class="bi bi-calculator me-1"></i>
                                    <?= number_format((float)$r['total_price'] / max(1, $jours), 2, ',', ' ') ?> €/jour
                                    × <?= $jours ?> jour<?= $jours > 1 ? 's' : '' ?>
                                </p>
                                <p class="fw-bold fs-5 mb-3">
                                    <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                                </p>
                                <form method="POST"
                                      action="<?= APP_URL ?>/reservations/<?= $r['id'] ?>/cancel"
                                      onsubmit="return confirm('Confirmer l\'annulation ?')">
                                    <?= Security::csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-x-circle me-1"></i>Annuler la réservation
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- EN COURS -->
    <div class="tab-pane fade" id="ongoing">
        <?php if (empty($ongoing)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-hourglass-split fs-1 d-block mb-2 opacity-25"></i>
                Aucune réservation en cours.
            </div>
        <?php else: ?>
            <?php foreach ($ongoing as $r):
                $jours = nbJours($r['start_date'], $r['end_date']);
            ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-success mb-1">En cours</span>
                                <h2 class="h6 fw-bold mb-1"><?= Security::e($r['title']) ?></h2>
                                <p class="small text-muted mb-1">
                                    <?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?>
                                </p>
                                <p class="small mb-0">
                                    <i class="bi bi-calendar-range me-1 text-muted"></i>
                                    <?= fmtDate($r['start_date']) ?> → <?= fmtDate($r['end_date']) ?>
                                    <span class="text-muted">(<?= $jours ?> jour<?= $jours > 1 ? 's' : '' ?>)</span>
                                </p>
                            </div>
                            <div class="text-end ms-3">
                                <div class="fw-bold fs-5">
                                    <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                                </div>
                                <small class="text-muted">
                                    <?= number_format((float)$r['total_price'] / max(1, $jours), 2, ',', ' ') ?> €/jour
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- HISTORIQUE -->
    <div class="tab-pane fade" id="past">
        <?php if (empty($past)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
                Aucune réservation passée.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Véhicule</th>
                            <th>Période</th>
                            <th>Durée</th>
                            <th class="text-end">Prix total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past as $r):
                            $jours = nbJours($r['start_date'], $r['end_date']);
                        ?>
                            <tr>
                                <td>
                                    <strong><?= Security::e($r['title']) ?></strong><br>
                                    <small class="text-muted">
                                        <?= Security::e($r['brand']) ?> <?= Security::e($r['model']) ?>
                                    </small>
                                </td>
                                <td class="small">
                                    <?= fmtDate($r['start_date']) ?><br>
                                    → <?= fmtDate($r['end_date']) ?>
                                </td>
                                <td class="small text-muted">
                                    <?= $jours ?> jour<?= $jours > 1 ? 's' : '' ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'cancelled'): ?>
                                        <span class="badge bg-secondary">Annulée</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark">Terminée</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<a href="<?= APP_URL ?>/vehicles" class="btn btn-warning mt-3 fw-semibold">
    <i class="bi bi-search me-1"></i>Réserver un nouveau véhicule
</a>
