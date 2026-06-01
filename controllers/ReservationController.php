<?php
declare(strict_types=1);

require_once ROOT_PATH . '/services/ReservationService.php';

/**
 * ReservationController – Création et annulation de réservations.
 */
class ReservationController extends Controller
{
    public function create(): void
    {
        Security::requireRole('client', 'owner', 'admin');
        Security::verifyCsrf();

        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $startDate = trim($_POST['start_date']   ?? '');
        $endDate   = trim($_POST['end_date']     ?? '');

        $result = (new ReservationService())->createReservation(
            $vehicleId,
            $startDate,
            $endDate,
            (int) Session::userId()
        );

        Session::flash($result['ok'] ? 'success' : 'error', $result['message']);
        $this->redirect($result['redirect']);
    }

    public function cancel(string $id): void
    {
        Security::requireRole('client', 'owner', 'admin');
        Security::verifyCsrf();

        $reservationId = (int) $id;
        $success       = (new ReservationService())->cancelReservation($reservationId, (int) Session::userId());

        if (!$success) {
            Session::flash('error', 'Annulation impossible. La réservation est peut-être déjà en cours ou terminée.');
        } else {
            Session::flash('success', 'Réservation annulée avec succès.');
        }

        $this->redirect('/dashboard/client');
    }
}
