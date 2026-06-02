<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/Reservation.php';

class OwnerController extends Controller
{
    public function reservations(): void
    {
        $reservations = (new Reservation())->findByOwner(Session::userId());

        $this->render('dashboard/owner/reservations', [
            'pageTitle'    => 'Réservations reçues – ' . APP_NAME,
            'reservations' => $reservations,
            'error'        => Session::getFlash('error'),
            'success'      => Session::getFlash('success'),
        ]);
    }
}
