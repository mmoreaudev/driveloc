<?php
declare(strict_types=1);

// ── Helpers locaux ────────────────────────────────────
function ownerFmtDate(string $d): string {
    return (new DateTimeImmutable($d))->format('d/m/Y');
}
function ownerNbJours(string $start, string $end): int {
    return (int) (new DateTimeImmutable($start))->diff(new DateTimeImmutable($end))->days;
}

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
        <i class="bi bi-speedometer2 me-2 text-warning"></i>Espace propriétaire
    </h1>
    <a href="<?= APP_URL ?>/vehicles/create" class="btn btn-dark btn-sm fw-semibold">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un véhicule
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
            <div class="fs-3 fw-bold text-dark"><?= count($vehicles) ?></div>
            <div class="small text-muted">
                Véhicule<?= count($vehicles) > 1 ? 's' : '' ?> publiés
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-primary"><?= (int)$stats['nb_upcoming'] ?></div>
            <div class="small text-muted">Réservations à venir</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-info"><?= (int)$stats['nb_clients'] ?></div>
            <div class="small text-muted">
                Client<?= (int)$stats['nb_clients'] > 1 ? 's' : '' ?> uniques
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 bg-dark text-white text-center py-3">
            <div class="fs-3 fw-bold text-warning">
                <?= number_format((float)$stats['total_revenue'], 2, ',', ' ') ?> €
            </div>
            <div class="small text-secondary">Chiffre d'affaires</div>
        </div>
    </div>

</div>

<!-- ═══════ ONGLETS ═══════════════════════════════════ -->
<ul class="nav nav-tabs mb-0" id="ownerTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                data-bs-target="#flotte" type="button" role="tab">
            <i class="bi bi-truck me-1"></i>Ma flotte
            <span class="badge bg-secondary ms-1"><?= count($vehicles) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
                data-bs-target="#res-upcoming" type="button" role="tab">
            <i class="bi bi-clock me-1"></i>À venir
            <?php if ((int)$stats['nb_upcoming'] > 0): ?>
                <span class="badge bg-primary ms-1"><?= (int)$stats['nb_upcoming'] ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
                data-bs-target="#res-ongoing" type="button" role="tab">
            <i class="bi bi-play-circle me-1"></i>En cours
            <?php if ((int)$stats['nb_ongoing'] > 0): ?>
                <span class="badge bg-success ms-1"><?= (int)$stats['nb_ongoing'] ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
                data-bs-target="#res-past" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i>Historique
            <?php $nbPast = (int)$stats['nb_done'] + (int)$stats['nb_cancelled']; ?>
            <?php if ($nbPast > 0): ?>
                <span class="badge bg-secondary ms-1"><?= $nbPast ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-4 bg-white shadow-sm mb-4">

    <!-- ══ MA FLOTTE ════════════════════════════════════ -->
    <div class="tab-pane fade show active" id="flotte" role="tabpanel">
        <?php if (empty($vehicles)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-truck fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-3">Vous n'avez encore publié aucun véhicule.</p>
                <a href="<?= APP_URL ?>/vehicles/create" class="btn btn-dark fw-semibold">
                    <i class="bi bi-plus-lg me-1"></i>Publier mon premier véhicule
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Photo</th>
                            <th>Véhicule</th>
                            <th>Immat.</th>
                            <th>Catégorie</th>
                            <th class="text-end">€ / jour</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td>
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
                                    <strong><?= Security::e($v['title']) ?></strong><br>
                                    <small class="text-muted">
                                        <?= Security::e($v['brand']) ?> <?= Security::e($v['model']) ?>
                                    </small>
                                </td>
                                <td><code class="small"><?= Security::e($v['registration']) ?></code></td>
                                <td class="small"><?= Security::e($v['category_name']) ?></td>
                                <td class="text-end fw-bold text-nowrap">
                                    <?= number_format((float)$v['price_per_day'], 2, ',', ' ') ?> €
                                </td>
                                <td class="text-center">
                                    <?php if ($v['status'] === 'active'): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= APP_URL ?>/vehicles/<?= (int)$v['id'] ?>"
                                       class="btn btn-outline-secondary btn-sm me-1" title="Voir l'annonce">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= APP_URL ?>/vehicles/<?= (int)$v['id'] ?>/edit"
                                       class="btn btn-outline-dark btn-sm me-1" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="<?= APP_URL ?>/vehicles/<?= (int)$v['id'] ?>/toggle"
                                          class="d-inline">
                                        <?= Security::csrfField() ?>
                                        <button type="submit"
                                                class="btn btn-sm me-1 <?= $v['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                title="<?= $v['status'] === 'active' ? 'Désactiver' : 'Activer' ?>">
                                            <i class="bi <?= $v['status'] === 'active' ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST"
                                          action="<?= APP_URL ?>/vehicles/<?= (int)$v['id'] ?>/delete"
                                          class="d-inline"
                                          onsubmit="return confirm('Supprimer ce véhicule définitivement ?')">
                                        <?= Security::csrfField() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ══ RÉSERVATIONS À VENIR ═════════════════════════ -->
    <div class="tab-pane fade" id="res-upcoming" role="tabpanel">
        <?php if (empty($resUpcoming)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-calendar-plus fs-1 d-block mb-2 opacity-25"></i>
                Aucune réservation à venir.
            </div>
        <?php else: ?>
            <?php renderReservationCards($resUpcoming, $badgeMap); ?>
        <?php endif; ?>
    </div>

    <!-- ══ RÉSERVATIONS EN COURS ════════════════════════ -->
    <div class="tab-pane fade" id="res-ongoing" role="tabpanel">
        <?php if (empty($resOngoing)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-hourglass-split fs-1 d-block mb-2 opacity-25"></i>
                Aucun véhicule en location actuellement.
            </div>
        <?php else: ?>
            <?php renderReservationCards($resOngoing, $badgeMap); ?>
        <?php endif; ?>
    </div>

    <!-- ══ HISTORIQUE ═══════════════════════════════════ -->
    <div class="tab-pane fade" id="res-past" role="tabpanel">
        <?php if (empty($resPast)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
                Aucune réservation passée.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Client</th>
                            <th>Véhicule</th>
                            <th>Dates</th>
                            <th class="text-center">Durée</th>
                            <th class="text-end">Prix total</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resPast as $r):
                            $jours = ownerNbJours($r['start_date'], $r['end_date']);
                            [$bg, $label] = $badgeMap[$r['status']] ?? ['bg-light text-dark', $r['status']];
                        ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold">
                                        <?= Security::e($r['client_firstname']) ?>
                                        <?= Security::e($r['client_lastname']) ?>
                                    </span><br>
                                    <small class="text-muted"><?= Security::e($r['client_email']) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($r['vehicle_image']): ?>
                                            <img src="<?= UPLOAD_URL . Security::e($r['vehicle_image']) ?>"
                                                 class="rounded object-fit-cover flex-shrink-0"
                                                 style="width:48px;height:36px" alt="">
                                        <?php endif; ?>
                                        <div>
                                            <strong class="small">
                                                <?= Security::e($r['vehicle_title']) ?>
                                            </strong><br>
                                            <span class="text-muted" style="font-size:.75rem">
                                                <?= Security::e($r['vehicle_brand']) ?>
                                                <?= Security::e($r['vehicle_model']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="small text-nowrap">
                                    <?= ownerFmtDate($r['start_date']) ?><br>
                                    <span class="text-muted">→ <?= ownerFmtDate($r['end_date']) ?></span>
                                </td>
                                <td class="text-center small text-muted"><?= $jours ?> j.</td>
                                <td class="text-end fw-bold text-nowrap">
                                    <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $bg ?>"><?= $label ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
/**
 * Affiche les cartes de réservations (À venir / En cours).
 */
function renderReservationCards(array $reservations, array $badgeMap): void {
    ?>
    <div class="row g-3">
        <?php foreach ($reservations as $r):
            $jours = ownerNbJours($r['start_date'], $r['end_date']);
            [$bg, $label] = $badgeMap[$r['status']] ?? ['bg-secondary', $r['status']];

            $joursEcoules  = (int)(new DateTimeImmutable('today'))
                                ->diff(new DateTimeImmutable($r['start_date']))->days;
            $joursRestants = max(0, $jours - $joursEcoules);
            $progression   = $r['status'] === 'ongoing' && $jours > 0
                                ? min(100, (int)round($joursEcoules / $jours * 100))
                                : 0;
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">

                    <?php if ($r['vehicle_image']): ?>
                        <img src="<?= UPLOAD_URL . Security::e($r['vehicle_image']) ?>"
                             class="card-img-top object-fit-cover" style="height:130px"
                             alt="<?= Security::e($r['vehicle_title']) ?>">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center"
                             style="height:130px">
                            <i class="bi bi-car-front text-secondary" style="font-size:2rem"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">

                        <!-- Statut + prix -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge <?= $bg ?>"><?= $label ?></span>
                            <span class="fw-bold">
                                <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                            </span>
                        </div>

                        <!-- Véhicule -->
                        <h2 class="h6 fw-bold mb-0"><?= Security::e($r['vehicle_title']) ?></h2>
                        <p class="small text-muted mb-2">
                            <?= Security::e($r['vehicle_brand']) ?>
                            <?= Security::e($r['vehicle_model']) ?>
                            <code class="ms-1" style="font-size:.7rem">
                                <?= Security::e($r['vehicle_registration']) ?>
                            </code>
                        </p>

                        <!-- Client -->
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center
                                        justify-content-center flex-shrink-0"
                                 style="width:32px;height:32px;font-size:.75rem">
                                <?= mb_strtoupper(mb_substr($r['client_firstname'], 0, 1))
                                   . mb_strtoupper(mb_substr($r['client_lastname'],  0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-semibold small">
                                    <?= Security::e($r['client_firstname']) ?>
                                    <?= Security::e($r['client_lastname']) ?>
                                </div>
                                <div class="text-muted" style="font-size:.75rem">
                                    <?= Security::e($r['client_email']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Période -->
                        <div class="bg-light rounded px-3 py-2 small mt-auto">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-muted">Début</div>
                                    <div class="fw-semibold"><?= ownerFmtDate($r['start_date']) ?></div>
                                </div>
                                <div class="text-muted d-flex align-items-center">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted">Fin</div>
                                    <div class="fw-semibold"><?= ownerFmtDate($r['end_date']) ?></div>
                                </div>
                            </div>
                            <div class="text-center text-muted mt-1">
                                <?= $jours ?> jour<?= $jours > 1 ? 's' : '' ?>
                                · <?= number_format((float)$r['vehicle_price_per_day'], 2, ',', ' ') ?> €/jour
                            </div>
                            <?php if ($r['status'] === 'ongoing'): ?>
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size:.7rem">
                                        <span>Progression</span>
                                        <span><?= $joursRestants ?> j. restant<?= $joursRestants > 1 ? 's' : '' ?></span>
                                    </div>
                                    <div class="progress" style="height:5px">
                                        <div class="progress-bar bg-success"
                                             style="width:<?= $progression ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}


 if ($error): ?>
    <div class="alert alert-danger"><?= Security::e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= Security::e($success) ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="<?= APP_URL ?>/dashboard/owner/reservations" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-inbox me-1"></i>Voir les réservations reçues
    </a>
</div>
