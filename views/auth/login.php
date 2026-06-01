<?php declare(strict_types=1); ?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body p-4">

                <h1 class="h4 fw-bold text-center mb-4">
                    <i class="bi bi-box-arrow-in-right me-2 text-warning"></i>Connexion
                </h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= Security::e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?= Security::e($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= APP_URL ?>/login" novalidate id="loginForm">
                    <?= Security::csrfField() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Adresse email</label>
                        <input type="email" id="email" name="email"
                               class="form-control" required autocomplete="email"
                               placeholder="vous@exemple.fr">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password"
                                   class="form-control" required autocomplete="current-password"
                                   placeholder="••••••••">
                            <button class="btn btn-outline-secondary" type="button"
                                    id="togglePwd" tabindex="-1" title="Afficher / masquer">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 text-end">
                        <small class="text-muted">Rôles disponibles :
                            <span class="badge bg-primary">client</span>
                            <span class="badge bg-warning text-dark">owner</span>
                            <span class="badge bg-danger">admin</span>
                        </small>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 fw-semibold" id="btnSubmit">
                        <span id="btnText">Se connecter</span>
                        <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </form>

                <script>
                (function () {
                    // Toggle visibilité mot de passe
                    const btn  = document.getElementById('togglePwd');
                    const pwd  = document.getElementById('password');
                    const icon = document.getElementById('eyeIcon');
                    if (btn) {
                        btn.addEventListener('click', function () {
                            const shown = pwd.type === 'text';
                            pwd.type    = shown ? 'password' : 'text';
                            icon.className = shown ? 'bi bi-eye' : 'bi bi-eye-slash';
                        });
                    }
                    // Indicateur de chargement à la soumission
                    document.getElementById('loginForm').addEventListener('submit', function () {
                        document.getElementById('btnText').textContent    = 'Connexion…';
                        document.getElementById('btnSpinner').classList.remove('d-none');
                        document.getElementById('btnSubmit').disabled = true;
                    });
                })();
                </script>

                <hr class="my-3">

                <p class="text-center mb-0 small">
                    Pas encore de compte ?
                    <a href="<?= APP_URL ?>/register" class="fw-semibold">Créer un compte</a>
                </p>

            </div>
        </div>

    </div>
</div>
