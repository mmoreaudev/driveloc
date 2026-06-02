<?php declare(strict_types=1); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">

        <div class="card border-0 mt-4" style="box-shadow: 2px 2px 4px 1px #252525;">
            <div class="card-body p-4">

                <h1 class="h4 fw-bold text-center mb-4">
                    <i class="bi bi-person-plus me-2 text-warning"></i>Créer un compte
                </h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= Security::e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= APP_URL ?>/register" novalidate>
                    <?= Security::csrfField() ?>

                    <div class="row g-3">

                        <div class="col-sm-6">
                            <label for="firstname" class="form-label fw-semibold">Prénom</label>
                            <input type="text" id="firstname" name="firstname"
                                   class="form-control" required autocomplete="given-name"
                                   placeholder="Jean">
                        </div>

                        <div class="col-sm-6">
                            <label for="lastname" class="form-label fw-semibold">Nom</label>
                            <input type="text" id="lastname" name="lastname"
                                   class="form-control" required autocomplete="family-name"
                                   placeholder="Dupont">
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold">Adresse email</label>
                            <input type="email" id="email" name="email"
                                   class="form-control" required autocomplete="email"
                                   placeholder="vous@exemple.fr">
                        </div>

                        <div class="col-12">
                            <label for="role" class="form-label fw-semibold">Je souhaite</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="client">Louer un véhicule (Client)</option>
                                <option value="owner">Proposer mes véhicules (Propriétaire)</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label for="password" class="form-label fw-semibold">Mot de passe</label>
                            <input type="password" id="password" name="password"
                                   class="form-control" required autocomplete="new-password"
                                   placeholder="Min. 8 car., 1 maj., 1 chiffre" minlength="8">
                            <div class="progress mt-1" style="height:4px">
                                <div id="pwdStrengthBar" class="progress-bar" style="width:0%"></div>
                            </div>
                            <div id="pwdStrengthLabel" class="form-text"></div>
                        </div>

                        <div class="col-sm-6">
                            <label for="password_confirm" class="form-label fw-semibold">Confirmation</label>
                            <input type="password" id="password_confirm" name="password_confirm"
                                   class="form-control" required autocomplete="new-password"
                                   placeholder="Retapez le mot de passe">
                            <div id="pwdMatchLabel" class="form-text"></div>
                        </div>

                        <script>
                        (function () {
                            const pwd     = document.getElementById('password');
                            const confirm = document.getElementById('password_confirm');
                            const bar     = document.getElementById('pwdStrengthBar');
                            const label   = document.getElementById('pwdStrengthLabel');
                            const match   = document.getElementById('pwdMatchLabel');

                            function strength(p) {
                                let score = 0;
                                if (p.length >= 8)               score++;
                                if (p.length >= 12)              score++;
                                if (/[A-Z]/.test(p))             score++;
                                if (/[0-9]/.test(p))             score++;
                                if (/[^A-Za-z0-9]/.test(p))     score++;
                                return score;
                            }

                            pwd.addEventListener('input', function () {
                                const s = strength(this.value);
                                const pct = s * 20;
                                const colors  = ['','bg-danger','bg-danger','bg-warning','bg-info','bg-success'];
                                const labels  = ['','Très faible','Faible','Moyen','Fort','Très fort'];
                                bar.style.width = pct + '%';
                                bar.className   = 'progress-bar ' + (colors[s] || '');
                                label.textContent = this.value ? labels[s] || '' : '';
                                label.className   = 'form-text ' + (s >= 4 ? 'text-success' : 'text-danger');
                            });

                            confirm.addEventListener('input', function () {
                                if (this.value === '') { match.textContent = ''; return; }
                                if (this.value === pwd.value) {
                                    match.textContent = '✓ Les mots de passe correspondent';
                                    match.className   = 'form-text text-success';
                                } else {
                                    match.textContent = '✗ Les mots de passe ne correspondent pas';
                                    match.className   = 'form-text text-danger';
                                }
                            });
                        })();
                        </script>

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-dark w-100 fw-semibold">
                                Créer mon compte
                            </button>
                        </div>

                    </div>
                </form>

                <hr class="my-3">

                <p class="text-center mb-0 small">
                    Déjà inscrit ?
                    <a href="<?= APP_URL ?>/login" class="fw-semibold">Se connecter</a>
                </p>

            </div>
        </div>

    </div>
</div>
