<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/Reservation.php';
require_once ROOT_PATH . '/models/Vehicle.php';

class DashboardController extends Controller
{
    public function client(): void
    {
        $this->requireRole('client', 'owner', 'admin');

        $reservationModel = new Reservation();
        $reservations     = $reservationModel->findByClient(Session::userId());
        $stats            = $reservationModel->statsForClient(Session::userId());

        $upcoming = array_values(array_filter($reservations, fn($r) => $r['status'] === 'upcoming'));
        $ongoing  = array_values(array_filter($reservations, fn($r) => $r['status'] === 'ongoing'));
        $past     = array_values(array_filter($reservations,
            fn($r) => in_array($r['status'], ['done', 'cancelled'], true)));

        $this->render('dashboard/client/index', [
            'pageTitle' => 'Mon espace – ' . APP_NAME,
            'upcoming'  => $upcoming,
            'ongoing'   => $ongoing,
            'past'      => $past,
            'stats'     => $stats,
            'error'     => Session::getFlash('error'),
            'success'   => Session::getFlash('success'),
        ]);
    }

    public function owner(): void
    {
        $this->requireRole('owner', 'admin');

        $ownerId          = Session::userId();
        $reservationModel = new Reservation();
        $vehicleModel     = new Vehicle();

        $vehicles     = $vehicleModel->findByOwner($ownerId);
        $reservations = $reservationModel->findByOwner($ownerId);
        $stats        = $reservationModel->statsForOwner($ownerId);

        $resUpcoming  = array_values(array_filter($reservations, fn($r) => $r['status'] === 'upcoming'));
        $resOngoing   = array_values(array_filter($reservations, fn($r) => $r['status'] === 'ongoing'));
        $resPast      = array_values(array_filter($reservations,
            fn($r) => in_array($r['status'], ['done', 'cancelled'], true)));

        $this->render('dashboard/owner/index', [
            'pageTitle'   => 'Espace propriétaire – ' . APP_NAME,
            'vehicles'    => $vehicles,
            'resUpcoming' => $resUpcoming,
            'resOngoing'  => $resOngoing,
            'resPast'     => $resPast,
            'stats'       => $stats,
            'error'       => Session::getFlash('error'),
            'success'     => Session::getFlash('success'),
        ]);
    }
}
