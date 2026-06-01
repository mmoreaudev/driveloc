<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Vehicle.php';
require_once ROOT_PATH . '/models/Reservation.php';
require_once ROOT_PATH . '/models/Category.php';

class AdminController extends Controller
{
    public function index(): void
    {
        Security::requireRole('admin');

        $reservationModel = new Reservation();
        $userModel        = new User();
        $vehicleModel     = new Vehicle();

        $this->render('dashboard/admin/index', [
            'pageTitle'     => 'Administration – ' . APP_NAME,
            'resStats'      => $reservationModel->globalStats(),
            'userStats'     => $userModel->statsByRole(),
            'vehicleStats'  => $vehicleModel->statsForAdmin(),
            'recentRes'     => $reservationModel->recentForAdmin(8),
            'error'         => Session::getFlash('error'),
            'success'       => Session::getFlash('success'),
        ]);
    }


    public function users(): void
    {
        Security::requireRole('admin');

        $this->render('dashboard/admin/users', [
            'pageTitle' => 'Gestion des utilisateurs – ' . APP_NAME,
            'users'     => (new User())->all(),
            'error'     => Session::getFlash('error'),
            'success'   => Session::getFlash('success'),
        ]);
    }

    public function toggleUser(string $id): void
    {
        Security::requireRole('admin');
        Security::verifyCsrf();

        $userId    = (int) $id;
        $userModel = new User();
        $user      = $userModel->findById($userId);

        if (!$user || $user['role'] === 'admin') {
            Session::flash('error', 'Action non autorisée.');
            $this->redirect('/dashboard/admin/users');
        }

        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $userModel->setStatus($userId, $newStatus);

        Session::flash('success', 'Statut du compte mis à jour.');
        $this->redirect('/dashboard/admin/users');
    }

    public function deleteUser(string $id): void
    {
        Security::requireRole('admin');
        Security::verifyCsrf();

        $userId = (int) $id;
        $user   = (new User())->findById($userId);

        if (!$user || $user['role'] === 'admin') {
            Session::flash('error', 'Action non autorisée.');
            $this->redirect('/dashboard/admin/users');
        }

        (new User())->delete($userId);

        Session::flash('success', 'Compte supprimé.');
        $this->redirect('/dashboard/admin/users');
    }


    public function vehicles(): void
    {
        Security::requireRole('admin');

        $this->render('dashboard/admin/vehicles', [
            'pageTitle' => 'Gestion des véhicules – ' . APP_NAME,
            'vehicles'  => (new Vehicle())->allForAdmin(),
            'error'     => Session::getFlash('error'),
            'success'   => Session::getFlash('success'),
        ]);
    }

    public function toggleVehicle(string $id): void
    {
        Security::requireRole('admin');
        Security::verifyCsrf();

        $vehicleId    = (int) $id;
        $vehicleModel = new Vehicle();
        $vehicle      = $vehicleModel->findById($vehicleId);

        if (!$vehicle) {
            Session::flash('error', 'Véhicule introuvable.');
            $this->redirect('/dashboard/admin/vehicles');
        }

        $newStatus = ($vehicle['status'] === 'active') ? 'inactive' : 'active';
        $vehicleModel->setStatus($vehicleId, $newStatus);

        Session::flash('success', 'Statut du véhicule mis à jour.');
        $this->redirect('/dashboard/admin/vehicles');
    }

    public function deleteVehicle(string $id): void
    {
        Security::requireRole('admin');
        Security::verifyCsrf();

        $vehicleId    = (int) $id;
        $vehicleModel = new Vehicle();
        $vehicle      = $vehicleModel->findById($vehicleId);

        if (!$vehicle) {
            Session::flash('error', 'Véhicule introuvable.');
            $this->redirect('/dashboard/admin/vehicles');
        }

        try {
            $vehicleDeleted = $vehicleModel->delete($vehicleId);

            if (!$vehicleDeleted) {
                Session::flash('error', 'Suppression impossible: véhicule introuvable ou déjà supprimé.');
                $this->redirect('/dashboard/admin/vehicles');
            }
        } catch (PDOException $e) {
            // Cas courant: des réservations référencent encore ce véhicule (FK RESTRICT).
            // On bascule en inactif pour le retirer du catalogue sans casser l'historique.
            $vehicleModel->setStatus($vehicleId, 'inactive');
            Session::flash('error', 'Suppression impossible: ce véhicule est lié à des réservations. Il a été désactivé.');
            $this->redirect('/dashboard/admin/vehicles');
        }

        if ($vehicle['main_image'] && file_exists(UPLOAD_PATH . $vehicle['main_image'])) {
            unlink(UPLOAD_PATH . $vehicle['main_image']);
        }

        Session::flash('success', 'Véhicule supprimé.');
        $this->redirect('/dashboard/admin/vehicles');
    }

    // ── Catégories ────────────────────────────────────

    public function categories(): void
    {
        Security::requireRole('admin');

        $this->render('dashboard/admin/categories', [
            'pageTitle'  => 'Gestion des catégories – ' . APP_NAME,
            'categories' => (new Category())->all(),
            'error'      => Session::getFlash('error'),
            'success'    => Session::getFlash('success'),
        ]);
    }

    public function createCategory(): void
    {
        Security::requireRole('admin');
        Security::verifyCsrf();

        $name          = trim($_POST['name'] ?? '');
        $categoryModel = new Category();

        if ($name === '') {
            Session::flash('error', 'Le nom de la catégorie est obligatoire.');
            $this->redirect('/dashboard/admin/categories');
        }

        if ($categoryModel->nameExists($name)) {
            Session::flash('error', 'Cette catégorie existe déjà.');
            $this->redirect('/dashboard/admin/categories');
        }

        $categoryModel->create($name);

        Session::flash('success', 'Catégorie créée.');
        $this->redirect('/dashboard/admin/categories');
    }
}
