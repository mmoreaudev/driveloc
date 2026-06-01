<?php declare(strict_types=1); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="bi bi-inbox me-2 text-warning"></i>Réservations reçues
    </h1>
    <a href="<?= APP_URL ?>/dashboard/owner" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Ma flotte
    </a>
</div>

<?php if (empty($reservations)): ?>
    <p class="text-muted">Aucune réservation reçue pour le moment.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Client</th>
                    <th>Véhicule</th>
                    <th>Dates</th>
                    <th>Prix total</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td>
                            <?= Security::e($r['client_firstname']) ?>
                            <?= Security::e($r['client_lastname']) ?>
                        </td>
                        <td><?= Security::e($r['title']) ?></td>
                        <td class="small">
                            <?= Security::e($r['start_date']) ?> → <?= Security::e($r['end_date']) ?>
                        </td>
                        <td class="fw-bold">
                            <?= number_format((float)$r['total_price'], 2, ',', ' ') ?> €
                        </td>
                        <td>
                            <?php $badges = [
                                'upcoming'  => ['bg-primary',   'À venir'],
                                'ongoing'   => ['bg-success',   'En cours'],
                                'done'      => ['bg-dark',      'Terminée'],
                                'cancelled' => ['bg-secondary', 'Annulée'],
                            ]; ?>
                            <?php [$bg, $label] = $badges[$r['status']] ?? ['bg-light text-dark', $r['status']]; ?>
                            <span class="badge <?= $bg ?>"><?= $label ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
