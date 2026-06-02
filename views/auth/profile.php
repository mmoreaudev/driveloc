<?php declare(strict_types=1); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <h1 class="h4 fw-bold mb-4">
            <i class="bi bi-person-circle me-2 text-warning"></i>Mon profil
        </h1>

        <!-- Badge rôle -->
        <div class="mb-4">
            <?php
            $roleMeta = [
                'client' => ['bg-primary',               'Client',        'bi-person',          'Vous pouvez rechercher et réserver des véhicules.'],
                'owner'  => ['bg-warning text-dark',     'Propriétaire',  'bi-truck',           'Vous pouvez publier des véhicules et recevoir des réservations.'],
                'admin'  => ['bg-danger',                'Administrateur','bi-shield-check',    'Vous disposez de tous les droits sur la plateforme.'],
            ];
            [$badgeCls, $roleLabel, $roleIcon, $roleDesc] = $roleMeta[Session::userRole()] ?? ['bg-secondary', 'Inconnu', 'bi-question', ''];
            ?>
            <div class="alert alert-light border d-flex align-items-center gap-3 py-2">
                <i class="bi <?= $roleIcon ?> fs-3 <?= str_contains($badgeCls, 'warning') ? 'text-warning' : (str_contains($badgeCls, 'danger') ? 'text-danger' : 'text-primary') ?>"></i>
                <div>
                    <span class="badge <?= $badgeCls ?> me-2"><?= $roleLabel ?></span>
                    <small class="text-muted"><?= $roleDesc ?></small>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <!-- ══ BLOC 1 : Informations personnelles ══ -->
            <div class="col-md-6">
                <div class="card border-0 h-100" style="box-shadow: 2px 2px 4px 1px #252525;">
                    <div class="card-header bg-dark text-white fw-semibold">
                        <i class="bi bi-person me-2"></i>Informations personnelles
                    </div>
                    <div class="card-body">

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i><?= Security::e($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                                <i class="bi bi-check-circle me-1"></i><?= Security::e($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/profile" novalidate>
                            <?= Security::csrfField() ?>

                            <div class="mb-3">
                                <label for="firstname" class="form-label fw-semibold small">Prénom</label>
                                <input type="text" id="firstname" name="firstname"
                                       class="form-control" required
                                       value="<?= Security::e($user['firstname'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="lastname" class="form-label fw-semibold small">Nom</label>
                                <input type="text" id="lastname" name="lastname"
                                       class="form-control" required
                                       value="<?= Security::e($user['lastname'] ?? '') ?>">
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold small">Adresse email</label>
                                <input type="email" id="email" name="email"
                                       class="form-control" required
                                       value="<?= Security::e($user['email'] ?? '') ?>">
                            </div>

                            <button type="submit" class="btn btn-dark w-100 fw-semibold">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer
                            </button>
                        </form>

                        <div class="mt-3 pt-3 border-top text-muted small">
                            <i class="bi bi-calendar me-1"></i>
                            Membre depuis le <?= Security::e(date('d/m/Y', strtotime($user['created_at'] ?? 'now'))) ?>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ══ BLOC 2 : Changer le mot de passe ══ -->
            <div class="col-md-6">
                <div class="card border-0 h-100" style="box-shadow: 2px 2px 4px 1px #252525;">
                    <div class="card-header bg-dark text-white fw-semibold">
                        <i class="bi bi-key me-2"></i>Changer le mot de passe
                    </div>
                    <div class="card-body">

                        <?php if ($errorPwd): ?>
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i><?= Security::e($errorPwd) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($successPwd): ?>
                            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                                <i class="bi bi-check-circle me-1"></i><?= Security::e($successPwd) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/profile/password" novalidate
                              id="pwdForm">
                            <?= Security::csrfField() ?>

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold small">
                                    Mot de passe actuel
                                </label>
                                <div class="input-group">
                                    <input type="password" id="current_password" name="current_password"
                                           class="form-control" required autocomplete="current-password">
                                    <button class="btn btn-outline-secondary toggle-pwd" type="button" tabindex="-1">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-semibold small">
                                    Nouveau mot de passe
                                </label>
                                <div class="input-group">
                                    <input type="password" id="new_password" name="new_password"
                                           class="form-control" required autocomplete="new-password"
                                           placeholder="Min. 8 car., 1 maj., 1 chiffre">
                                    <button class="btn btn-outline-secondary toggle-pwd" type="button" tabindex="-1">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <!-- Barre de force -->
                                <div class="progress mt-1" style="height:4px">
                                    <div id="newPwdBar" class="progress-bar" style="width:0%"></div>
                                </div>
                                <div id="newPwdLabel" class="form-text"></div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-semibold small">
                                    Confirmation
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       class="form-control" required autocomplete="new-password">
                                <div id="confirmLabel" class="form-text"></div>
                            </div>

                            <!-- Règles de complexité -->
                            <ul class="list-unstyled small text-muted mb-3" id="pwdRules">
                                <li id="rule-len"><i class="bi bi-x-circle text-danger me-1"></i>Au moins 8 caractères</li>
                                <li id="rule-upper"><i class="bi bi-x-circle text-danger me-1"></i>Au moins une majuscule</li>
                                <li id="rule-num"><i class="bi bi-x-circle text-danger me-1"></i>Au moins un chiffre</li>
                            </ul>

                            <button type="submit" class="btn btn-warning w-100 fw-semibold">
                                <i class="bi bi-lock me-1"></i>Modifier le mot de passe
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div><!-- /.row -->
    </div><!-- /.col -->
</div><!-- /.row -->

<div class="mt-3">
    <a href="<?= APP_URL ?>/dashboard/<?= Security::e(Session::userRole()) ?>"
       class="btn btn-link text-muted ps-0">
        <i class="bi bi-arrow-left me-1"></i>Retour au tableau de bord
    </a>
</div>

<script>
(function () {
    // ── Toggle visibilité ─────────────────────────────
    document.querySelectorAll('.toggle-pwd').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon  = this.querySelector('i');
            const shown = input.type === 'text';
            input.type     = shown ? 'password' : 'text';
            icon.className = shown ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    });

    // ── Force du mot de passe ─────────────────────────
    const newPwd      = document.getElementById('new_password');
    const bar         = document.getElementById('newPwdBar');
    const barLabel    = document.getElementById('newPwdLabel');
    const confirmPwd  = document.getElementById('confirm_password');
    const confirmLbl  = document.getElementById('confirmLabel');

    const rules = {
        'rule-len':   p => p.length >= 8,
        'rule-upper': p => /[A-Z]/.test(p),
        'rule-num':   p => /[0-9]/.test(p),
    };

    function updateRules(p) {
        Object.entries(rules).forEach(([id, fn]) => {
            const li   = document.getElementById(id);
            const ok   = fn(p);
            const icon = li.querySelector('i');
            icon.className = ok
                ? 'bi bi-check-circle text-success me-1'
                : 'bi bi-x-circle text-danger me-1';
        });
    }

    function strength(p) {
        let s = 0;
        if (p.length >= 8)               s++;
        if (p.length >= 12)              s++;
        if (/[A-Z]/.test(p))             s++;
        if (/[0-9]/.test(p))             s++;
        if (/[^A-Za-z0-9]/.test(p))     s++;
        return s;
    }

    newPwd.addEventListener('input', function () {
        const s = strength(this.value);
        const colors = ['','bg-danger','bg-danger','bg-warning','bg-info','bg-success'];
        const labels = ['','Très faible','Faible','Moyen','Fort','Très fort'];
        bar.style.width = (s * 20) + '%';
        bar.className   = 'progress-bar ' + (colors[s] || '');
        barLabel.textContent = this.value ? (labels[s] || '') : '';
        barLabel.className   = 'form-text ' + (s >= 4 ? 'text-success' : 'text-muted');
        updateRules(this.value);
        // Re-vérifier la confirmation si déjà remplie
        if (confirmPwd.value) confirmPwd.dispatchEvent(new Event('input'));
    });

    confirmPwd.addEventListener('input', function () {
        if (!this.value) { confirmLbl.textContent = ''; return; }
        if (this.value === newPwd.value) {
            confirmLbl.textContent = '✓ Les mots de passe correspondent';
            confirmLbl.className   = 'form-text text-success';
        } else {
            confirmLbl.textContent = '✗ Les mots de passe ne correspondent pas';
            confirmLbl.className   = 'form-text text-danger';
        }
    });
})();
</script>
