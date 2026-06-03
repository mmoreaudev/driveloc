<?php declare(strict_types=1); ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger mb-4"><?= ($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success mb-4"><?= ($success) ?></div>
<?php endif; ?>

<?php
$categoryIcons = [
    'suv' => 'bi-truck-front',
    'citadine' => 'bi-car-front',
    'berline' => 'bi-car-front-fill',
    'familiale' => 'bi-people',
    'utilitaire' => 'bi-box-seam',
    'luxe' => 'bi-stars',
    'sport' => 'bi-lightning-charge',
    'electrique' => 'bi-ev-front',
    '4x4' => 'bi-sign-turn-right',
    'cabriolet' => 'bi-sun',
    'moto' => 'bi-bicycle',
];

$pickCategoryIcon = static function (string $categoryName) use ($categoryIcons): string {
    $slug = strtolower($categoryName);

    foreach ($categoryIcons as $keyword => $icon) {
        if (str_contains($slug, $keyword)) {
            return $icon;
        }
    }

    return 'bi-car-front';
};

$landingCategories = array_slice($categories, 0, 8);
?>

<section class="landing-hero mb-5">
    <div class="row g-4 align-items-center">
        <div class="col-lg-7">
            <h1 class="mb-3">Louez un vehicule sans friction</h1>
            <p class="lead text-muted mb-4">
                Trouvez rapidement le modele ideal pour un week-end, un demenagement ou un depart en vacances.
                Reservez en quelques clics avec des proprietaires verifies.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-dark btn-lg" href="<?= APP_URL ?>/vehicles">
                    <i class="bi bi-search me-1"></i>Voir les vehicules
                </a>
                <a class="btn btn-outline-dark btn-lg" href="<?= APP_URL ?>/register">
                    <i class="bi bi-person-plus me-1"></i>Creer un compte
                </a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="landing-panel p-4 h-100">
                <h2 class="h5 mb-3">Pourquoi choisir DriveLoc</h2>
                <ul class="list-unstyled mb-0 d-grid gap-3">
                    <li><i class="bi bi-check2-circle text-warning me-2"></i>Recherche par dates et disponibilite reelle</li>
                    <li><i class="bi bi-check2-circle text-warning me-2"></i>Paiement transparent au prix par jour</li>
                    <li><i class="bi bi-check2-circle text-warning me-2"></i>Flotte multi-categories en constante evolution</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
        <h2 class="h4 mb-0">Explorer par categorie</h2>
        <a href="<?= APP_URL ?>/vehicles" class="btn btn-sm btn-outline-dark">Tout afficher</a>
    </div>

    <?php if (empty($landingCategories)): ?>
        <div class="alert alert-warning mb-0">Aucune categorie n'est disponible pour le moment.</div>
    <?php else: ?>
        <div class="landing-category-chips">
            <?php foreach ($landingCategories as $index => $category): ?>
                <a class="landing-category-chip landing-category-tone-<?= ($index % 4) + 1 ?>"
                   href="<?= APP_URL ?>/vehicles?category_id=<?= (int) $category['id'] ?>">
                    <span class="landing-category-icon">
                        <i class="bi <?= $pickCategoryIcon((string) $category['name']) ?>"></i>
                    </span>
                    <span class="landing-category-label"><?= ($category['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
        <h2 class="h4 mb-0">Vehicules a la une</h2>
        <a href="<?= APP_URL ?>/vehicles" class="btn btn-sm btn-dark">Voir tous les vehicules</a>
    </div>

    <?php if (empty($featuredVehicles)): ?>
        <div class="alert alert-warning mb-0">Aucun vehicule n'est disponible pour le moment.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <?php foreach ($featuredVehicles as $vehicle): ?>
                <div class="col">
                    <a href="<?= APP_URL ?>/vehicles/<?= (int) $vehicle['id'] ?>" class="text-decoration-none text-dark h-100">
                    <article class="card h-100 border-0">
                        <?php if (!empty($vehicle['main_image'])): ?>
                            <img src="<?= ($vehicle['main_image']) ?>" class="card-img-top object-fit-cover" style="height: 210px" alt="<?= ($vehicle['title']) ?>" loading="lazy" decoding="async">
                        <?php else: ?>
                            <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 210px">
                                <i class="bi bi-car-front text-white" style="font-size: 3rem"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-warning text-dark mb-2 w-fit"><?= ($vehicle['category_name']) ?></span>
                            <h3 class="h5 mb-1"><?= ($vehicle['title']) ?></h3>
                            <p class="text-muted mb-3"><?= ($vehicle['brand']) ?> <?= ($vehicle['model']) ?></p>

                            <p class="fw-bold mb-3">
                                <?= number_format((float) $vehicle['price_per_day'], 2, ',', ' ') ?> EUR / jour
                            </p>

                            <a href="<?= APP_URL ?>/vehicles/<?= (int) $vehicle['id'] ?>" class="btn btn-outline-dark mt-auto">
                                Voir le detail
                            </a>
                        </div>
                    </article>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
