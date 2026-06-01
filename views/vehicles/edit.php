<?php declare(strict_types=1); ?>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <h1 class="h4 fw-bold mb-4">
            <i class="bi bi-pencil-square me-2 text-warning"></i>
            Modifier : <?= Security::e($vehicle['title']) ?>
        </h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= Security::e($error) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <form method="POST"
                      action="<?= APP_URL ?>/vehicles/<?= $vehicle['id'] ?>/edit"
                      enctype="multipart/form-data" novalidate>
                    <?= Security::csrfField() ?>

                    <div class="row g-3">

                        <div class="col-12">
                            <label for="title" class="form-label fw-semibold">Titre *</label>
                            <input type="text" id="title" name="title"
                                   class="form-control" required
                                   value="<?= Security::e($vehicle['title']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="brand" class="form-label fw-semibold">Marque *</label>
                            <input type="text" id="brand" name="brand"
                                   class="form-control" required
                                   value="<?= Security::e($vehicle['brand']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="model" class="form-label fw-semibold">Modèle *</label>
                            <input type="text" id="model" name="model"
                                   class="form-control" required
                                   value="<?= Security::e($vehicle['model']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="registration" class="form-label fw-semibold">Immatriculation *</label>
                            <input type="text" id="registration" name="registration"
                                   class="form-control" required
                                   value="<?= Security::e($vehicle['registration']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Catégorie *</label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ((int)$cat['id'] === (int)$vehicle['category_id']) ? 'selected' : '' ?>>
                                        <?= Security::e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="price_per_day" class="form-label fw-semibold">Prix / jour (€) *</label>
                            <input type="number" id="price_per_day" name="price_per_day"
                                   class="form-control" required min="1" step="0.01"
                                   value="<?= Security::e($vehicle['price_per_day']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="main_image" class="form-label fw-semibold">Changer la photo</label>
                            <?php if ($vehicle['main_image']): ?>
                                <div class="mb-2">
                                    <img src="<?= UPLOAD_URL . Security::e($vehicle['main_image']) ?>"
                                         class="img-thumbnail" style="height:80px" alt="Photo actuelle">
                                    <small class="text-muted d-block">Photo actuelle</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="main_image" name="main_image"
                                   class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Laisser vide pour conserver la photo actuelle.</div>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"><?= Security::e($vehicle['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <a href="<?= APP_URL ?>/dashboard/owner" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-dark fw-semibold">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
