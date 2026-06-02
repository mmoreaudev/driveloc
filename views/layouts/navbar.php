<?php declare(strict_types=1); ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="box-shadow: 0 2px 4px rgba(0,0,0,0.5);">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold fs-4" href="<?= APP_URL ?>">
            <i class="bi bi-car-front-fill me-2 text-warning"></i>DriveLoc
        </a>

        <!-- Toggler mobile -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMain"
                aria-controls="navMain" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <!-- Liens gauche -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/vehicles">
                        <i class="bi bi-search me-1"></i>Véhicules
                    </a>
                </li>
            </ul>

            <!-- Liens droite -->
            <ul class="navbar-nav align-items-lg-center gap-1">
                <?php if (Session::isLoggedIn()): ?>

                    <!-- Dashboard selon le rôle -->
                    <?php if (Session::userRole() === 'owner' || Session::userRole() === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= APP_URL ?>/vehicles/create">
                                <i class="bi bi-plus-circle me-1"></i>Ajouter un véhicule
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                           href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <?= Security::e(Session::userFirstname()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= APP_URL ?>/profile">
                                    <i class="bi bi-person-gear me-2"></i>Mon profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= APP_URL ?>/dashboard/client">
                                    <i class="bi bi-calendar-check me-2"></i>Mes réservations
                                </a>
                            </li>
                            <?php if (Session::userRole() === 'owner' || Session::userRole() === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/dashboard/owner">
                                        <i class="bi bi-truck me-2"></i>Ma flotte
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/dashboard/owner/reservations">
                                        <i class="bi bi-inbox me-2"></i>Réservations reçues
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (Session::userRole() === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= APP_URL ?>/dashboard/admin">
                                        <i class="bi bi-speedometer2 me-2"></i>Administration
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm fw-semibold px-3"
                           href="<?= APP_URL ?>/register">
                            Inscription
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
