<?php declare(strict_types=1); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">
        <i class="bi bi-tags me-2 text-warning"></i>Gestion des catégories
    </h1>
    <a href="<?= APP_URL ?>/dashboard/admin" class="btn btn-outline-dark btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Tableau de bord
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= Security::e($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= Security::e($success) ?></div>
<?php endif; ?>

<div class="row g-4">

    <!-- Liste -->
    <div class="col-md-7">
        <div class="card border-0" style="box-shadow: 2px 2px 4px 1px #252525;">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3">Catégories existantes</h2>
                <ul class="list-group list-group-flush">
                    <?php foreach ($categories as $cat): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-tag me-2 text-muted"></i><?= Security::e($cat['name']) ?></span>
                            <span class="badge bg-secondary">#<?= $cat['id'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Formulaire ajout -->
    <div class="col-md-5">
        <div class="card border-0" style="box-shadow: 2px 2px 4px 1px #252525;">
            <div class="card-body">
                <h2 class="h6 fw-bold mb-3">Ajouter une catégorie</h2>
                <form method="POST" action="<?= APP_URL ?>/dashboard/admin/categories/create">
                    <?= Security::csrfField() ?>
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold">Nom de la catégorie</label>
                        <input type="text" id="name" name="name"
                               class="form-control" required
                               placeholder="ex: Camping-car">
                    </div>
                    <button type="submit" class="btn btn-dark w-100 fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
