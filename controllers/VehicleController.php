<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/Vehicle.php';
require_once ROOT_PATH . '/models/Category.php';
require_once ROOT_PATH . '/models/Reservation.php';

class VehicleController extends Controller
{
    private Vehicle $vehicleModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->vehicleModel = new Vehicle();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        $categoryId = isset($_GET['category_id']) && ctype_digit($_GET['category_id']) ? (int) $_GET['category_id'] : null;
        $brand = isset($_GET['brand']) ? trim($_GET['brand']) : null;
        $maxPrice = isset($_GET['max_price']) ? trim($_GET['max_price']) : null;
        $startRaw = isset($_GET['start_date']) ? trim($_GET['start_date']) : null;
        $endRaw = isset($_GET['end_date']) ? trim($_GET['end_date']) : null;

        $dateError = $this->validateDates($startRaw, $endRaw, $startDate, $endDate);

        $this->render('vehicles/index', [
            'pageTitle'  => 'Véhicules disponibles – ' . APP_NAME,
            'vehicles'   => $this->vehicleModel->search(['category_id' => $categoryId, 'brand' => $brand ?: null, 'max_price' => $maxPrice ?: null, 'start_date' => $startDate, 'end_date' => $endDate]),
            'categories' => $this->categoryModel->all(),
            'filters'    => ['category_id' => $categoryId, 'brand' => $brand, 'max_price' => $maxPrice, 'start_date' => $startDate, 'end_date' => $endDate],
            'raw'        => ['start_date' => $startRaw ?? '', 'end_date' => $endRaw ?? '', 'brand' => $brand ?? '', 'max_price' => $maxPrice ?? '', 'category_id' => $categoryId ?? ''],
            'dateError'  => $dateError,
            'error'      => Session::getFlash('error'),
            'success'    => Session::getFlash('success'),
        ]);
    }

    public function show(string $id): void
    {
        $vehicle = $this->vehicleModel->findById((int) $id);
        
        if (!$vehicle || $vehicle['status'] === 'inactive') {
            http_response_code(404);
            require VIEWS_PATH . '/errors/404.php';
            return;
        }

        $this->render('vehicles/show', [
            'pageTitle'      => $vehicle['title'] . ' – ' . APP_NAME,
            'vehicle'        => $vehicle,
            'blockedPeriods' => (new Reservation())->getBlockedPeriods((int) $id),
            'error'          => Session::getFlash('error'),
            'success'        => Session::getFlash('success'),
        ]);
    }

    public function createForm(): void
    {
        $this->render('vehicles/create', [
            'pageTitle'  => 'Ajouter un véhicule – ' . APP_NAME,
            'categories' => $this->categoryModel->all(),
            'error'      => Session::getFlash('error'),
        ]);
    }

    public function create(): void
    {
        $data = $this->extractVehicleData();
        $error = $this->validateVehicleData($data);
        
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/vehicles/create');
        }

        $data['owner_id'] = Session::userId();
        $this->vehicleModel->create($data);
        Session::flash('success', 'Véhicule ajouté avec succès.');
        $this->redirect('/dashboard/owner');
    }

    public function editForm(string $id): void
    {
        $vehicle = $this->getOwnedVehicle((int) $id);
        $this->render('vehicles/edit', [
            'pageTitle'  => 'Modifier le véhicule – ' . APP_NAME,
            'vehicle'    => $vehicle,
            'categories' => $this->categoryModel->all(),
            'error'      => Session::getFlash('error'),
        ]);
    }

    public function edit(string $id): void
    {
        $this->getOwnedVehicle((int) $id);
        $data = $this->extractVehicleData();
        $error = $this->validateVehicleData($data);
        
        if ($error) {
            Session::flash('error', $error);
            $this->redirect('/vehicles/' . $id . '/edit');
        }

        $this->vehicleModel->update((int) $id, $data);
        Session::flash('success', 'Véhicule modifié avec succès.');
        $this->redirect('/dashboard/owner');
    }

    public function delete(string $id): void
    {
        $this->getOwnedVehicle((int) $id);
        $this->vehicleModel->delete((int) $id);
        Session::flash('success', 'Véhicule supprimé.');
        $this->redirect('/dashboard/owner');
    }

    public function toggleStatus(string $id): void
    {
        $vehicle = $this->getOwnedVehicle((int) $id);
        $newStatus = $vehicle['status'] === 'active' ? 'inactive' : 'active';
        $this->vehicleModel->setStatus((int) $id, $newStatus);
        Session::flash('success', "Véhicule " . ($newStatus === 'active' ? 'activé' : 'désactivé') . ".");
        $this->redirect('/dashboard/owner');
    }

    private function extractVehicleData(): array
    {
        return [
            'title'        => trim($_POST['title'] ?? ''),
            'brand'        => trim($_POST['brand'] ?? ''),
            'model'        => trim($_POST['model'] ?? ''),
            'registration' => trim($_POST['registration'] ?? ''),
            'category_id'  => (int) ($_POST['category_id'] ?? 0),
            'price_per_day'=> (float) ($_POST['price_per_day'] ?? 0),
            'description'  => trim($_POST['description'] ?? ''),
            'main_image'   => $this->vehicleModel->validateImageUrl(trim($_POST['main_image'] ?? '')) ?? null,
        ];
    }

    private function validateVehicleData(array $data): ?string
    {
        if (!$data['title'] || !$data['brand'] || !$data['model'] || !$data['registration'] || !$data['category_id'] || $data['price_per_day'] <= 0) {
            return 'Tous les champs obligatoires doivent être remplis.';
        }

        if (mb_strlen($data['title']) > 100 || mb_strlen($data['brand']) > 50
            || mb_strlen($data['model']) > 50 || mb_strlen($data['registration']) > 20
            || mb_strlen($data['description']) > 2000 || $data['price_per_day'] > 9999.99) {
            return 'Une ou plusieurs valeurs dépassent la taille autorisée.';
        }

        if (!preg_match('/^[A-Z0-9\- ]{2,20}$/i', $data['registration'])) {
            return 'Format d\'immatriculation invalide.';
        }

        if (isset($_POST['main_image']) && trim($_POST['main_image']) && !$data['main_image']) {
            return 'Lien image invalide. Utilisez une URL complète commençant par http:// ou https://.';
        }

        return null;
    }

    private function validateDates(?string $startRaw, ?string $endRaw, ?string &$startDate, ?string &$endDate): ?string
    {
        $startDate = $endDate = null;
        $today = new DateTimeImmutable('today');

        if ($startRaw) {
            $s = DateTimeImmutable::createFromFormat('Y-m-d', $startRaw);
            if (!$s) return 'La date de début est invalide.';
            if ($s < $today) return 'La date de début ne peut pas être dans le passé.';
            $startDate = $startRaw;
        }

        if ($endRaw) {
            $e = DateTimeImmutable::createFromFormat('Y-m-d', $endRaw);
            if (!$e) return 'La date de fin est invalide.';
            if ($startDate && $endRaw <= $startDate) return 'La date de fin doit être postérieure à la date de début.';
            $endDate = $endRaw;
        }

        if (($startDate === null) !== ($endDate === null)) {
            $startDate = $endDate = null;
            return 'Veuillez renseigner à la fois la date de début et la date de fin.';
        }

        return null;
    }

    public function landing(): void
    {
        $vehicles = (new Vehicle())->search();

        $this->render('home/index', [
            'pageTitle'        => 'Location de vehicules – ' . APP_NAME,
            'featuredVehicles' => array_slice($vehicles, 0, 6),
            'categories'       => (new Category())->all(),
            'error'            => Session::getFlash('error'),
            'success'          => Session::getFlash('success'),
        ]);
    }


    private function getOwnedVehicle(int $id): array
    {
        $vehicle = $this->vehicleModel->findById($id);
        if (!$vehicle) {
            http_response_code(404);
            require VIEWS_PATH . '/errors/404.php';
            exit;
        }
        return $vehicle;
    }
}