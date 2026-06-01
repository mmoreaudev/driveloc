<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/Vehicle.php';
require_once ROOT_PATH . '/models/Category.php';
require_once ROOT_PATH . '/models/Reservation.php';

/**
 * VehicleController – Listing, recherche, CRUD véhicules.
 */
class VehicleController extends Controller
{
    // ── Public ────────────────────────────────────────

    public function index(): void
    {
        $vehicleModel  = new Vehicle();
        $categoryModel = new Category();

        // ── Lecture et nettoyage des filtres GET ──────
        $categoryId = isset($_GET['category_id']) && ctype_digit($_GET['category_id'])
            ? (int) $_GET['category_id'] : null;

        $brand    = isset($_GET['brand'])     ? trim($_GET['brand'])     : null;
        $maxPrice = isset($_GET['max_price']) ? trim($_GET['max_price']) : null;
        $startRaw = isset($_GET['start_date']) ? trim($_GET['start_date']) : null;
        $endRaw   = isset($_GET['end_date'])   ? trim($_GET['end_date'])   : null;

        // ── Validation des dates ──────────────────────
        $dateError  = null;
        $startDate  = null;
        $endDate    = null;
        $today      = new DateTimeImmutable('today');

        if ($startRaw !== null && $startRaw !== '') {
            $s = DateTimeImmutable::createFromFormat('Y-m-d', $startRaw);
            if ($s === false) {
                $dateError = 'La date de début est invalide.';
            } elseif ($s < $today) {
                $dateError = 'La date de début ne peut pas être dans le passé.';
            } else {
                $startDate = $startRaw;
            }
        }

        if ($dateError === null && $endRaw !== null && $endRaw !== '') {
            $e = DateTimeImmutable::createFromFormat('Y-m-d', $endRaw);
            if ($e === false) {
                $dateError = 'La date de fin est invalide.';
            } elseif ($startDate !== null && $endRaw <= $startDate) {
                $dateError = 'La date de fin doit être postérieure à la date de début.';
            } else {
                $endDate = $endRaw;
            }
        }

        // Si une seule des deux dates est fournie, on ignore les deux
        if (($startDate === null) !== ($endDate === null)) {
            $startDate = null;
            $endDate   = null;
            if ($dateError === null) {
                $dateError = 'Veuillez renseigner à la fois la date de début et la date de fin.';
            }
        }

        $filters = [
            'category_id' => $categoryId,
            'brand'       => ($brand    !== '') ? $brand    : null,
            'max_price'   => ($maxPrice !== '') ? $maxPrice : null,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
        ];

        $this->render('vehicles/index', [
            'pageTitle'  => 'Véhicules disponibles – ' . APP_NAME,
            'vehicles'   => $vehicleModel->search($filters),
            'categories' => $categoryModel->all(),
            'filters'    => $filters,
            'raw'        => [               // valeurs brutes pour repeupler le form
                'start_date'  => $startRaw  ?? '',
                'end_date'    => $endRaw    ?? '',
                'brand'       => $brand     ?? '',
                'max_price'   => $maxPrice  ?? '',
                'category_id' => $categoryId ?? '',
            ],
            'dateError'  => $dateError,
            'error'      => Session::getFlash('error'),
            'success'    => Session::getFlash('success'),
        ]);
    }

    public function show(string $id): void
    {
        $vehicle = (new Vehicle())->findById((int) $id);

        if (!$vehicle || $vehicle['status'] === 'inactive') {
            http_response_code(404);
            require VIEWS_PATH . '/errors/404.php';
            return;
        }

        // Périodes déjà réservées → sérialisées en JSON pour le JS du formulaire
        $reservationModel = new Reservation();
        $blockedPeriods   = $reservationModel->getBlockedPeriods((int) $id);

        $this->render('vehicles/show', [
            'pageTitle'      => Security::e($vehicle['title']) . ' – ' . APP_NAME,
            'vehicle'        => $vehicle,
            'blockedPeriods' => $blockedPeriods,
            'error'          => Session::getFlash('error'),
            'success'        => Session::getFlash('success'),
        ]);
    }

    // ── Owner : création ──────────────────────────────

    public function createForm(): void
    {
        Security::requireRole('owner', 'admin');

        $this->render('vehicles/create', [
            'pageTitle'  => 'Ajouter un véhicule – ' . APP_NAME,
            'categories' => (new Category())->all(),
            'error'      => Session::getFlash('error'),
        ]);
    }

    public function create(): void
    {
        Security::requireRole('owner', 'admin');
        Security::verifyCsrf();

        $title        = trim($_POST['title']        ?? '');
        $brand        = trim($_POST['brand']        ?? '');
        $model        = trim($_POST['model']        ?? '');
        $registration = trim($_POST['registration'] ?? '');
        $categoryId   = (int) ($_POST['category_id']  ?? 0);
        $pricePerDay  = (float) ($_POST['price_per_day'] ?? 0);
        $description  = trim($_POST['description']  ?? '');

        if ($title === '' || $brand === '' || $model === '' || $registration === '' || $categoryId === 0 || $pricePerDay <= 0) {
            Session::flash('error', 'Tous les champs obligatoires doivent être remplis.');
            $this->redirect('/vehicles/create');
        }

        // Limites de longueur (protection contre dépassement de colonne SQL et garbage data)
        if (mb_strlen($title) > 100 || mb_strlen($brand) > 50
            || mb_strlen($model) > 50 || mb_strlen($registration) > 20
            || mb_strlen($description) > 2000 || $pricePerDay > 9999.99) {
            Session::flash('error', 'Une ou plusieurs valeurs dépassent la taille autorisée.');
            $this->redirect('/vehicles/create');
        }

        // Format immatriculation : lettres, chiffres et tirets uniquement
        if (!preg_match('/^[A-Z0-9\- ]{2,20}$/i', $registration)) {
            Session::flash('error', 'Format d\'immatriculation invalide.');
            $this->redirect('/vehicles/create');
        }

        $vehicleModel = new Vehicle();
        $mainImage    = null;

        if (!empty($_FILES['main_image']['name'])) {
            $mainImage = $vehicleModel->handleImageUpload($_FILES['main_image']);
            if ($mainImage === null) {
                Session::flash('error', 'Image invalide. Formats acceptés : JPG, PNG, WEBP (max 5 Mo).');
                $this->redirect('/vehicles/create');
            }
        }

        $vehicleModel->create([
            'owner_id'     => Session::userId(),
            'category_id'  => $categoryId,
            'title'        => $title,
            'brand'        => $brand,
            'model'        => $model,
            'registration' => $registration,
            'price_per_day'=> $pricePerDay,
            'description'  => $description,
            'main_image'   => $mainImage,
        ]);

        Session::flash('success', 'Véhicule ajouté avec succès.');
        $this->redirect('/dashboard/owner');
    }

    // ── Owner : modification ──────────────────────────

    public function editForm(string $id): void
    {
        Security::requireRole('owner', 'admin');

        $vehicle = $this->getOwnedVehicle((int) $id);

        $this->render('vehicles/edit', [
            'pageTitle'  => 'Modifier le véhicule – ' . APP_NAME,
            'vehicle'    => $vehicle,
            'categories' => (new Category())->all(),
            'error'      => Session::getFlash('error'),
        ]);
    }

    public function edit(string $id): void
    {
        Security::requireRole('owner', 'admin');
        Security::verifyCsrf();

        $vehicle      = $this->getOwnedVehicle((int) $id);
        $vehicleModel = new Vehicle();

        $title        = trim($_POST['title']        ?? '');
        $brand        = trim($_POST['brand']        ?? '');
        $model        = trim($_POST['model']        ?? '');
        $registration = trim($_POST['registration'] ?? '');
        $categoryId   = (int) ($_POST['category_id']   ?? 0);
        $pricePerDay  = (float) ($_POST['price_per_day']  ?? 0);
        $description  = trim($_POST['description']  ?? '');

        if ($title === '' || $brand === '' || $model === '' || $registration === '' || $categoryId === 0 || $pricePerDay <= 0) {
            Session::flash('error', 'Tous les champs obligatoires doivent être remplis.');
            $this->redirect('/vehicles/' . $id . '/edit');
        }

        // Limites de longueur
        if (mb_strlen($title) > 100 || mb_strlen($brand) > 50
            || mb_strlen($model) > 50 || mb_strlen($registration) > 20
            || mb_strlen($description) > 2000 || $pricePerDay > 9999.99) {
            Session::flash('error', 'Une ou plusieurs valeurs dépassent la taille autorisée.');
            $this->redirect('/vehicles/' . $id . '/edit');
        }

        if (!preg_match('/^[A-Z0-9\- ]{2,20}$/i', $registration)) {
            Session::flash('error', 'Format d\'immatriculation invalide.');
            $this->redirect('/vehicles/' . $id . '/edit');
        }

        $mainImage = $vehicle['main_image'];

        if (!empty($_FILES['main_image']['name'])) {
            $uploaded = $vehicleModel->handleImageUpload($_FILES['main_image']);
            if ($uploaded === null) {
                Session::flash('error', 'Image invalide. Formats acceptés : JPG, PNG, WEBP (max 5 Mo).');
                $this->redirect('/vehicles/' . $id . '/edit');
            }
            // Supprime l'ancienne image
            if ($mainImage && file_exists(UPLOAD_PATH . $mainImage)) {
                unlink(UPLOAD_PATH . $mainImage);
            }
            $mainImage = $uploaded;
        }

        $vehicleModel->update((int) $id, [
            'category_id'  => $categoryId,
            'title'        => $title,
            'brand'        => $brand,
            'model'        => $model,
            'registration' => $registration,
            'price_per_day'=> $pricePerDay,
            'description'  => $description,
            'main_image'   => $mainImage,
        ]);

        Session::flash('success', 'Véhicule modifié avec succès.');
        $this->redirect('/dashboard/owner');
    }

    // ── Owner : suppression ───────────────────────────

    public function delete(string $id): void
    {
        Security::requireRole('owner', 'admin');
        Security::verifyCsrf();

        $vehicle      = $this->getOwnedVehicle((int) $id);
        $vehicleModel = new Vehicle();

        if ($vehicle['main_image'] && file_exists(UPLOAD_PATH . $vehicle['main_image'])) {
            unlink(UPLOAD_PATH . $vehicle['main_image']);
        }

        $vehicleModel->delete((int) $id);

        Session::flash('success', 'Véhicule supprimé.');
        $this->redirect('/dashboard/owner');
    }

    // ── Owner : toggle statut ─────────────────────────

    public function toggleStatus(string $id): void
    {
        Security::requireRole('owner', 'admin');
        Security::verifyCsrf();

        $vehicle = $this->getOwnedVehicle((int) $id);
        $newStatus = $vehicle['status'] === 'active' ? 'inactive' : 'active';

        (new Vehicle())->setStatus((int) $id, $newStatus);

        $label = $newStatus === 'active' ? 'activé' : 'désactivé';
        Session::flash('success', "Véhicule {$label}.");
        $this->redirect('/dashboard/owner');
    }

    // ── Helpers privés ────────────────────────────────

    /**
     * Récupère un véhicule et vérifie que l'utilisateur en est le propriétaire.
     * Les admins peuvent accéder à tous les véhicules.
     */
    private function getOwnedVehicle(int $id): array
    {
        $vehicle = (new Vehicle())->findById($id);

        if (!$vehicle) {
            http_response_code(404);
            require VIEWS_PATH . '/errors/404.php';
            exit;
        }

        if (
            Session::userRole() !== 'admin'
            && (int) $vehicle['owner_id'] !== Session::userId()
        ) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        return $vehicle;
    }
}
