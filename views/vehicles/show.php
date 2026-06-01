<?php declare(strict_types=1); ?>

<div class="row g-4">

    <!-- Photo + infos -->
    <div class="col-lg-7">
        <?php if ($vehicle['main_image']): ?>
            <img src="<?= UPLOAD_URL . Security::e($vehicle['main_image']) ?>"
                 class="img-fluid rounded shadow-sm w-100 object-fit-cover"
                 style="max-height:420px"
                 alt="<?= Security::e($vehicle['title']) ?>">
        <?php else: ?>
            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                 style="height:300px">
                <i class="bi bi-car-front text-white" style="font-size:4rem"></i>
            </div>
        <?php endif; ?>
    </div>

    <!-- Détails + formulaire de réservation -->
    <div class="col-lg-5">

        <span class="badge bg-warning text-dark mb-2"><?= Security::e($vehicle['category_name']) ?></span>
        <h1 class="h3 fw-bold"><?= Security::e($vehicle['title']) ?></h1>
        <p class="text-muted mb-1">
            <i class="bi bi-person me-1"></i>
            Proposé par <strong><?= Security::e($vehicle['firstname'] . ' ' . $vehicle['lastname']) ?></strong>
        </p>
        <p class="text-muted mb-1">
            <i class="bi bi-car-front me-1"></i>
            <?= Security::e($vehicle['brand']) ?> <?= Security::e($vehicle['model']) ?>
        </p>
        <p class="text-muted mb-3">
            <i class="bi bi-credit-card me-1"></i>
            Immatriculation : <strong><?= Security::e($vehicle['registration']) ?></strong>
        </p>

        <?php if ($vehicle['description']): ?>
            <p class="mb-4"><?= Security::e($vehicle['description']) ?></p>
        <?php endif; ?>

        <div class="card bg-dark text-white border-0 shadow mb-4 p-3 text-center">
            <span class="fs-2 fw-bold">
                <?= number_format((float)$vehicle['price_per_day'], 2, ',', ' ') ?> €
            </span>
            <small class="text-secondary">par jour</small>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= Security::e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= Security::e($success) ?></div>
        <?php endif; ?>

        <!-- Formulaire de réservation -->
        <?php if (Session::isLoggedIn()):
            // Pré-remplissage depuis la recherche (GET passé par la fiche listing)
            $preStart = isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date'], ENT_QUOTES) : '';
            $preEnd   = isset($_GET['end_date'])   ? htmlspecialchars($_GET['end_date'],   ENT_QUOTES) : '';
            // Périodes bloquées → JSON pour le JS
            // JSON_HEX_* : échappe tous les caractères dangereux en contexte HTML inline
            $blockedJson = json_encode(
                $blockedPeriods ?? [],
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );
        ?>
            <form method="POST" action="<?= APP_URL ?>/reservations/create" id="formReservation">
                <?= Security::csrfField() ?>
                <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Date de début</label>
                        <input type="date" name="start_date" id="start_date"
                               class="form-control" required
                               min="<?= date('Y-m-d') ?>"
                               value="<?= $preStart ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Date de fin</label>
                        <input type="date" name="end_date" id="end_date"
                               class="form-control" required
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                               value="<?= $preEnd ?>">
                    </div>
                </div>

                <!-- Alerte conflit de disponibilité (JS) -->
                <div id="conflictAlert" class="alert alert-danger small d-none">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <span id="conflictText"></span>
                </div>

                <!-- Récapitulatif prix (JS) -->
                <div id="priceSummary" class="alert alert-success border-0 small d-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="priceSummaryText"></span>
                        <strong id="priceTotal" class="fs-5"></strong>
                    </div>
                </div>

                <button type="submit" id="btnReserve" class="btn btn-warning fw-bold w-100">
                    <i class="bi bi-calendar-check me-2"></i>Réserver ce véhicule
                </button>
            </form>

            <?php if (!empty($blockedPeriods)): ?>
                <div class="mt-3 p-2 border rounded small text-muted">
                    <i class="bi bi-calendar-x me-1 text-danger"></i>
                    <strong>Périodes déjà réservées :</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        <?php foreach ($blockedPeriods as $p): ?>
                            <li>
                                <?= (new DateTimeImmutable($p['start']))->format('d/m/Y') ?>
                                →
                                <?= (new DateTimeImmutable($p['end']))->format('d/m/Y') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= APP_URL ?>/login" class="btn btn-outline-dark w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Connectez-vous pour réserver
            </a>
        <?php endif; ?>

    </div>
</div>

<div class="mt-3">
    <a href="<?= APP_URL ?>/vehicles" class="btn btn-link text-muted ps-0">
        <i class="bi bi-arrow-left me-1"></i>Retour aux véhicules
    </a>
</div>

<script>
(function () {
    const pricePerDay    = <?= (float) $vehicle['price_per_day'] ?>;
    const blockedPeriods = <?= $blockedJson ?? '[]' ?>;

    const startInput    = document.getElementById('start_date');
    const endInput      = document.getElementById('end_date');
    const summary       = document.getElementById('priceSummary');
    const summaryText   = document.getElementById('priceSummaryText');
    const priceTotal    = document.getElementById('priceTotal');
    const conflictAlert = document.getElementById('conflictAlert');
    const conflictText  = document.getElementById('conflictText');
    const btnReserve    = document.getElementById('btnReserve');

    // ── Couplage des champs de dates ──────────────────
    startInput.addEventListener('change', function () {
        if (!this.value) return;
        const next = new Date(this.value);
        next.setDate(next.getDate() + 1);
        endInput.min = next.toISOString().split('T')[0];
        if (endInput.value && endInput.value <= this.value) endInput.value = '';
        update();
    });
    endInput.addEventListener('change', update);

    // ── Détection de conflit ──────────────────────────
    function overlaps(selStart, selEnd) {
        for (const p of blockedPeriods) {
            // Chevauchement si : p.start < selEnd  AND  p.end > selStart
            if (p.start < selEnd && p.end > selStart) {
                return { start: p.start, end: p.end };
            }
        }
        return null;
    }

    function fmt(dateStr) {
        const [y, m, d] = dateStr.split('-');
        return `${d}/${m}/${y}`;
    }

    // ── Mise à jour de l'interface ────────────────────
    function update() {
        if (!startInput.value || !endInput.value) {
            summary.classList.add('d-none');
            conflictAlert.classList.add('d-none');
            btnReserve.disabled = false;
            return;
        }

        const start = startInput.value;
        const end   = endInput.value;
        const days  = Math.round((new Date(end) - new Date(start)) / 86400000);

        if (days <= 0) {
            summary.classList.add('d-none');
            return;
        }

        // Vérification de chevauchement côté JS (doublon de la vérification serveur)
        const conflict = overlaps(start, end);
        if (conflict) {
            conflictText.textContent =
                `Ce véhicule est déjà réservé du ${fmt(conflict.start)} au ${fmt(conflict.end)}.`;
            conflictAlert.classList.remove('d-none');
            summary.classList.add('d-none');
            btnReserve.disabled = true;
            return;
        }

        conflictAlert.classList.add('d-none');
        btnReserve.disabled = false;

        // Calcul du prix total
        const total = (pricePerDay * days).toFixed(2).replace('.', ',');
        summaryText.textContent =
            `${days} jour${days > 1 ? 's' : ''} × ${pricePerDay.toFixed(2).replace('.', ',')} € / jour`;
        priceTotal.textContent = `${total} €`;
        summary.classList.remove('d-none');
    }

    // Déclencher si les dates sont pré-remplies (depuis la recherche)
    if (startInput.value && endInput.value) update();
})();
</script>
