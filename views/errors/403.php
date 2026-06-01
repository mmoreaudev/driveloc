<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 – Accès refusé | DriveLoc</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="text-center p-4">
        <i class="bi bi-shield-exclamation text-danger" style="font-size:4rem"></i>
        <h1 class="display-6 fw-bold mt-3">403 – Accès refusé</h1>
        <p class="text-muted">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>" class="btn btn-dark mt-2">
            <i class="bi bi-house me-1"></i>Retour à l'accueil
        </a>
    </div>
</body>
</html>
