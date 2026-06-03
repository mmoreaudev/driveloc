<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Service indisponible | DriveLoc</title>
    <link rel="icon" type="image/svg+xml" href="<?= (defined('APP_URL') ? APP_URL : '') ?>/assets/img/driveloc-icon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center p-4" style="max-width:760px">
        <img src="<?= (defined('APP_URL') ? APP_URL : '') ?>/assets/img/driveloc-icon.svg" alt="DriveLoc" style="width:56px;height:56px" class="mb-2">
        <i class="bi bi-tools text-warning" style="font-size:4rem"></i>
        <h1 class="display-6 fw-bold mt-3">503 - Service temporairement indisponible</h1>
        <p class="text-muted mb-3">
            L'application est en cours d'initialisation. Merci de reessayer dans quelques instants.
        </p>

        <?php if (!empty($maintenanceMessage)): ?>
            <div class="alert alert-warning text-start">
                <strong>Details:</strong><br>
                <?= ((string) $maintenanceMessage) ?>
            </div>
        <?php endif; ?>

        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>" class="btn btn-dark mt-2">
            <i class="bi bi-arrow-clockwise me-1"></i>Reessayer
        </a>
    </div>
</body>
</html>

