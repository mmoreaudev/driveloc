<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/Reservation.php';
require_once ROOT_PATH . '/models/Vehicle.php';

final class ReservationService
{
    public function createReservation(
        int $vehicleId,
        string $startDate,
        string $endDate,
        int $clientId
    ): array {
        if ($vehicleId === 0 || $startDate === '' || $endDate === '') {
            return [
                'ok' => false,
                'message' => 'Donnees de reservation incompletes.',
                'redirect' => '/vehicles/' . $vehicleId,
            ];
        }

        $start = \DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        $end   = \DateTimeImmutable::createFromFormat('Y-m-d', $endDate);

        if (!$start || !$end || $end <= $start) {
            return [
                'ok' => false,
                'message' => 'Les dates selectionnees sont invalides.',
                'redirect' => '/vehicles/' . $vehicleId,
            ];
        }

        $today = new \DateTimeImmutable('today');
        if ($start < $today) {
            return [
                'ok' => false,
                'message' => 'La date de debut ne peut pas etre dans le passe.',
                'redirect' => '/vehicles/' . $vehicleId,
            ];
        }

        $vehicle = (new Vehicle())->findById($vehicleId);
        if (!$vehicle || $vehicle['status'] !== 'active') {
            return [
                'ok' => false,
                'message' => 'Ce vehicule n\'est pas disponible a la location.',
                'redirect' => '/vehicles',
            ];
        }

        $reservationModel = new Reservation();
        if (!$reservationModel->isAvailable($vehicleId, $startDate, $endDate)) {
            return [
                'ok' => false,
                'message' => 'Ce vehicule est deja reserve sur cette periode. Veuillez choisir d\'autres dates.',
                'redirect' => '/vehicles/' . $vehicleId,
            ];
        }

        $nbDays     = $start->diff($end)->days;
        $totalPrice = round((float) $vehicle['price_per_day'] * $nbDays, 2);

        $reservationModel->create(
            $vehicleId,
            $clientId,
            $startDate,
            $endDate,
            $totalPrice
        );

        return [
            'ok' => true,
            'message' => 'Reservation confirmee ! Retrouvez-la dans votre espace client.',
            'redirect' => '/dashboard/client',
        ];
    }

    public function cancelReservation(int $reservationId, int $clientId): bool
    {
        return (new Reservation())->cancel($reservationId, $clientId);
    }
}
