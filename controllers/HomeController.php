<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/Vehicle.php';
require_once ROOT_PATH . '/models/Category.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $vehicleModel = new Vehicle();
        $categoryModel = new Category();

        $vehicles = $vehicleModel->search();

        $this->render('home/index', [
            'pageTitle' => 'Location de vehicules - ' . APP_NAME,
            'featuredVehicles' => array_slice($vehicles, 0, 6),
            'categories' => $categoryModel->all(),
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ]);
    }
}
