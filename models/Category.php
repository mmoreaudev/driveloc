<?php
declare(strict_types=1);

require_once ROOT_PATH . '/core/Model.php';

/**
 * Category – Référentiel des catégories de véhicules.
 */
class Category extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM categories ORDER BY name ASC');
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne('SELECT * FROM categories WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function nameExists(string $name): bool
    {
        return $this->fetchOne(
            'SELECT id FROM categories WHERE name = :name LIMIT 1',
            ['name' => $name]
        ) !== false;
    }

    public function create(string $name): int
    {
        $this->query('INSERT INTO categories (name) VALUES (:name)', ['name' => $name]);
        return $this->lastId();
    }
}
