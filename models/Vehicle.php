<?php
declare(strict_types=1);

require_once ROOT_PATH . '/core/Model.php';

class Vehicle extends Model
{
    // Recherche avec NOT EXISTS pour meilleures performances
    public function search(array $filters = []): array
    {
        $sql = '
            SELECT v.*, c.name AS category_name, u.firstname, u.lastname
            FROM vehicles v
            JOIN categories c ON c.id = v.category_id
            JOIN users      u ON u.id = v.owner_id
            WHERE v.status = :status
        ';
        $params = ['status' => 'active'];

        if (!empty($filters['category_id'])) {
            $sql .= ' AND v.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['brand'])) {
            $sql .= ' AND v.brand LIKE :brand';
            $params['brand'] = '%' . $filters['brand'] . '%';
        }

        if (!empty($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= ' AND v.price_per_day <= :max_price';
            $params['max_price'] = (float) $filters['max_price'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= "
                AND NOT EXISTS (
                    SELECT 1
                    FROM reservations r
                    WHERE r.vehicle_id  = v.id
                      AND r.status     NOT IN ('cancelled')
                      AND r.start_date  < :end_date
                      AND r.end_date    > :start_date
                )
            ";
            $params['start_date'] = $filters['start_date'];
            $params['end_date']   = $filters['end_date'];
        }

        $sql .= ' ORDER BY v.created_at DESC';

        return $this->fetchAll($sql, $params);
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne('
            SELECT v.*, c.name AS category_name, u.firstname, u.lastname, u.id AS owner_user_id
            FROM vehicles v
            JOIN categories c ON c.id = v.category_id
            JOIN users      u ON u.id = v.owner_id
            WHERE v.id = :id LIMIT 1
        ', ['id' => $id]);
    }


    public function findByOwner(int $ownerId): array
    {
        return $this->fetchAll('
            SELECT v.*, c.name AS category_name
            FROM vehicles v
            JOIN categories c ON c.id = v.category_id
            WHERE v.owner_id = :owner_id
            ORDER BY v.created_at DESC
        ', ['owner_id' => $ownerId]);
    }

    public function allForAdmin(): array
    {
        return $this->fetchAll('
            SELECT v.*, c.name AS category_name, u.firstname, u.lastname
            FROM vehicles v
            JOIN categories c ON c.id = v.category_id
            JOIN users      u ON u.id = v.owner_id
            ORDER BY v.created_at DESC
        ');
    }

    public function countAll(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS n FROM vehicles')['n'] ?? 0);
    }

    public function statsForAdmin(): array
    {
        $row = $this->fetchOne("
            SELECT
                COUNT(*)                    AS total,
                SUM(status = 'active')      AS nb_active,
                SUM(status = 'inactive')    AS nb_inactive
            FROM vehicles
        ");

        return $row ?: ['total' => 0, 'nb_active' => 0, 'nb_inactive' => 0];
    }


    public function create(array $data): int
    {
        $this->query('
            INSERT INTO vehicles
                (owner_id, category_id, title, brand, model, registration, price_per_day, description, main_image)
            VALUES
                (:owner_id, :category_id, :title, :brand, :model, :registration, :price_per_day, :description, :main_image)
        ', $data);
        return $this->lastId();
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        return $this->execute('
            UPDATE vehicles
            SET category_id   = :category_id,
                title         = :title,
                brand         = :brand,
                model         = :model,
                registration  = :registration,
                price_per_day = :price_per_day,
                description   = :description,
                main_image    = :main_image
            WHERE id = :id
        ', $data);
    }

    public function setStatus(int $id, string $status): bool
    {
        return $this->execute(
            'UPDATE vehicles SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM vehicles WHERE id = :id', ['id' => $id]);
    }


    public function validateImageUrl(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        if (mb_strlen($url) > 255) {
            return null;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!is_string($scheme) || !in_array(strtolower($scheme), ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }
}
