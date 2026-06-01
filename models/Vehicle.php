<?php
declare(strict_types=1);

require_once ROOT_PATH . '/core/Model.php';

/**
 * Vehicle – Gestion des véhicules publiés sur la plateforme.
 */
class Vehicle extends Model
{
    // ── Lecture publique ──────────────────────────────

    /**
     * Recherche avec filtres (catégorie, disponibilité, marque, prix max).
     *
     * Disponibilité : utilise NOT EXISTS plutôt que NOT IN pour de meilleures
     * performances — MySQL peut court-circuiter dès le premier conflit trouvé
     * dans la sous-requête, sans matérialiser l'ensemble des vehicle_id bloqués.
     *
     * Condition de chevauchement : la réservation R bloque le créneau [S, E] si
     *   R.start_date < E  AND  R.end_date > S  (intersection non nulle)
     */
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
            // NOT EXISTS : plus efficace que NOT IN sur grands volumes
            // L'index (vehicle_id, status, start_date, end_date) est utilisé
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

    // ── Lecture propriétaire / admin ──────────────────

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

    /**
     * Statistiques agrégées pour le dashboard admin.
     */
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

    // ── Écriture ──────────────────────────────────────

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

    // ── Upload image ──────────────────────────────────

    /**
     * Déplace et valide un fichier image uploadé.
     * Retourne le nom de fichier final ou null en cas d'erreur.
     */
    public function handleImageUpload(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            return null;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ALLOWED_IMG_MIME, true)) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMG_EXT, true)) {
            return null;
        }

        // Vérification supplémentaire : s'assurer que le binaire est réellement une image.
        // getimagesize() analyse les en-têtes du fichier, pas seulement l'extension.
        // Défense en profondeur contre les fichiers polyglot (ex: image.php.jpg valide en MIME).
        if (@getimagesize($file['tmp_name']) === false) {
            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest     = UPLOAD_PATH . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return $filename;
    }
}
