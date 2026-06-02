<?php
declare(strict_types=1);

require_once ROOT_PATH . '/core/Model.php';

class Reservation extends Model
{
    public function isAvailable(int $vehicleId, string $startDate, string $endDate): bool
    {
        $row = $this->fetchOne("
            SELECT 1 AS conflict
            FROM reservations
            WHERE vehicle_id  = :vehicle_id
              AND status      NOT IN ('cancelled')
              AND start_date  < :end_date
              AND end_date    > :start_date
            LIMIT 1
        ", ['vehicle_id' => $vehicleId, 'start_date' => $startDate, 'end_date' => $endDate]);

        return $row === false;
    }

    public function getBlockedPeriods(int $vehicleId): array
    {
        return $this->fetchAll("
            SELECT start_date AS start, end_date AS end
            FROM reservations
            WHERE vehicle_id = :vehicle_id
              AND status     NOT IN ('cancelled')
              AND end_date   >= CURDATE()
            ORDER BY start_date
        ", ['vehicle_id' => $vehicleId]);
    }


    public function create(
        int    $vehicleId,
        int    $clientId,
        string $startDate,
        string $endDate,
        float  $totalPrice
    ): int {
        $this->query('
            INSERT INTO reservations (vehicle_id, client_id, start_date, end_date, total_price)
            VALUES (:vehicle_id, :client_id, :start_date, :end_date, :total_price)
        ', [
            'vehicle_id'  => $vehicleId,
            'client_id'   => $clientId,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'total_price' => $totalPrice,
        ]);
        return $this->lastId();
    }


    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT * FROM reservations WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByClient(int $clientId): array
    {
        return $this->fetchAll('
            SELECT r.*, v.title, v.brand, v.model, v.main_image, v.price_per_day, v.id AS vehicle_id_ref
            FROM reservations r
            JOIN vehicles v ON v.id = r.vehicle_id
            WHERE r.client_id = :client_id
            ORDER BY r.start_date DESC
        ', ['client_id' => $clientId]);
    }

    public function statsForClient(int $clientId): array
    {
        $row = $this->fetchOne("
            SELECT
                COUNT(*)                                          AS total,
                COALESCE(SUM(CASE WHEN status != 'cancelled'
                               THEN total_price END), 0)         AS total_spent,
                SUM(status = 'upcoming')                         AS nb_upcoming,
                SUM(status = 'ongoing')                          AS nb_ongoing,
                SUM(status = 'done')                             AS nb_done,
                SUM(status = 'cancelled')                        AS nb_cancelled
            FROM reservations
            WHERE client_id = :client_id
        ", ['client_id' => $clientId]);

        return $row ?: [
            'total' => 0, 'total_spent' => 0,
            'nb_upcoming' => 0, 'nb_ongoing' => 0,
            'nb_done' => 0, 'nb_cancelled' => 0,
        ];
    }

    public function findByOwner(int $ownerId): array
    {
        return $this->fetchAll("
            SELECT
                r.id,
                r.vehicle_id,
                r.client_id,
                r.start_date,
                r.end_date,
                r.total_price,
                r.status,
                r.created_at,
                v.title           AS vehicle_title,
                v.brand           AS vehicle_brand,
                v.model           AS vehicle_model,
                v.registration    AS vehicle_registration,
                v.main_image      AS vehicle_image,
                v.price_per_day   AS vehicle_price_per_day,
                u.firstname       AS client_firstname,
                u.lastname        AS client_lastname,
                u.email           AS client_email
            FROM reservations r
            INNER JOIN vehicles v ON v.id = r.vehicle_id
            INNER JOIN users    u ON u.id = r.client_id
            WHERE v.owner_id = :owner_id
            ORDER BY r.start_date DESC
        ", ['owner_id' => $ownerId]);
    }

    public function statsForOwner(int $ownerId): array
    {
        $row = $this->fetchOne("
            SELECT
                COUNT(*)                                                           AS total_reservations,
                COUNT(DISTINCT r.client_id)                                        AS nb_clients,
                COALESCE(SUM(CASE WHEN r.status NOT IN ('cancelled')
                                  THEN r.total_price END), 0)                      AS total_revenue,
                SUM(r.status = 'upcoming')                                         AS nb_upcoming,
                SUM(r.status = 'ongoing')                                          AS nb_ongoing,
                SUM(r.status = 'done')                                             AS nb_done,
                SUM(r.status = 'cancelled')                                        AS nb_cancelled
            FROM reservations r
            INNER JOIN vehicles v ON v.id = r.vehicle_id
            WHERE v.owner_id = :owner_id
        ", ['owner_id' => $ownerId]);

        return $row ?: [
            'total_reservations' => 0, 'nb_clients'    => 0,
            'total_revenue'      => 0, 'nb_upcoming'   => 0,
            'nb_ongoing'         => 0, 'nb_done'       => 0,
            'nb_cancelled'       => 0,
        ];
    }

    public function cancel(int $id, int $clientId): bool
    {
        return $this->execute("
            UPDATE reservations
            SET status = 'cancelled'
            WHERE id = :id
              AND client_id = :client_id
              AND status = 'upcoming'
        ", ['id' => $id, 'client_id' => $clientId]);
    }


    public function countByStatus(string $status): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS n FROM reservations WHERE status = :status',
            ['status' => $status]
        );
        return (int) ($row['n'] ?? 0);
    }

    public function totalRevenue(): float
    {
        $row = $this->fetchOne(
            "SELECT COALESCE(SUM(total_price), 0) AS revenue
             FROM reservations WHERE status != 'cancelled'"
        );
        return (float) ($row['revenue'] ?? 0.0);
    }

    public function globalStats(): array
    {
        $row = $this->fetchOne("
            SELECT
                COUNT(*)                                                              AS total_reservations,
                SUM(status = 'upcoming')                                              AS nb_upcoming,
                SUM(status = 'ongoing')                                               AS nb_ongoing,
                SUM(status = 'done')                                                  AS nb_done,
                SUM(status = 'cancelled')                                             AS nb_cancelled,
                COALESCE(SUM(CASE WHEN status NOT IN ('cancelled')
                                  THEN total_price END), 0)                           AS revenue_theoretical,
                COALESCE(SUM(CASE WHEN status = 'done'
                                  THEN total_price END), 0)                           AS revenue_confirmed
            FROM reservations
        ");

        return $row ?: [
            'total_reservations' => 0, 'nb_upcoming'          => 0,
            'nb_ongoing'         => 0, 'nb_done'              => 0,
            'nb_cancelled'       => 0, 'revenue_theoretical'  => 0,
            'revenue_confirmed'  => 0,
        ];
    }

    public function recentForAdmin(int $limit = 10): array
    {
        return $this->fetchAll("
            SELECT
                r.id,
                r.start_date,
                r.end_date,
                r.total_price,
                r.status,
                r.created_at,
                v.title         AS vehicle_title,
                v.brand         AS vehicle_brand,
                v.model         AS vehicle_model,
                v.main_image    AS vehicle_image,
                u.firstname     AS client_firstname,
                u.lastname      AS client_lastname
            FROM reservations r
            INNER JOIN vehicles v ON v.id = r.vehicle_id
            INNER JOIN users    u ON u.id = r.client_id
            ORDER BY r.created_at DESC
            LIMIT :lim
        ", ['lim' => $limit]);
    }
}
