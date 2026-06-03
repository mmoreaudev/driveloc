<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= ($pageTitle ?? APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/driveloc-icon.svg">
    <link rel="shortcut icon" href="<?= APP_URL ?>/assets/img/driveloc-icon.svg">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <style>
  .dropdown-menu {
    z-index: 9999 !important;
  }
  .navbar {
    z-index: 1050;
  }
</style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container py-4 flex-grow-1">

