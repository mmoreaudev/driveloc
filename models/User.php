<?php
declare(strict_types=1);

require_once ROOT_PATH . '/core/Model.php';

class User extends Model
{
    public function findById(int $id): array|false
    {
        return $this->fetchOne(
            'SELECT id, firstname, lastname, email, role, status, created_at
             FROM users WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByEmail(string $email): array|false
    {
        return $this->fetchOne(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $row = $this->fetchOne(
                'SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1',
                ['email' => $email, 'id' => $excludeId]
            );
        } else {
            $row = $this->fetchOne(
                'SELECT id FROM users WHERE email = :email LIMIT 1',
                ['email' => $email]
            );
        }
        return $row !== false;
    }

    public function all(): array
    {
        return $this->fetchAll(
            'SELECT id, firstname, lastname, email, role, status, created_at
             FROM users ORDER BY created_at DESC'
        );
    }

    public function countAll(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS n FROM users')['n'] ?? 0);
    }

    public function statsByRole(): array
    {
        $row = $this->fetchOne("
            SELECT
                COUNT(*)                   AS total,
                SUM(role = 'client')       AS nb_clients,
                SUM(role = 'owner')        AS nb_owners,
                SUM(role = 'admin')        AS nb_admins,
                SUM(status = 'active')     AS nb_active,
                SUM(status = 'inactive')   AS nb_inactive
            FROM users
        ");

        return $row ?: [
            'total'       => 0, 'nb_clients'  => 0,
            'nb_owners'   => 0, 'nb_admins'   => 0,
            'nb_active'   => 0, 'nb_inactive' => 0,
        ];
    }

    public function create(
        string $firstname,
        string $lastname,
        string $email,
        string $password,
        string $role = 'client'
    ): int {
        $this->query(
            'INSERT INTO users (firstname, lastname, email, password, role)
             VALUES (:firstname, :lastname, :email, :password, :role)',
            [
                'firstname' => $firstname,
                'lastname'  => $lastname,
                'email'     => $email,
                'password'  => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'role'      => $role,
            ]
        );
        return $this->lastId();
    }

    public function updateProfile(int $id, string $firstname, string $lastname, string $email): bool
    {
        return $this->execute(
            'UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email WHERE id = :id',
            ['firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'id' => $id]
        );
    }

    public function setStatus(int $id, string $status): bool
    {
        return $this->execute(
            'UPDATE users SET status = :status WHERE id = :id',
            ['status' => $status, 'id' => $id]
        );
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM users WHERE id = :id', ['id' => $id]);
    }

    public function verifyPassword(int $id, string $plainPassword): bool
    {
        $row = $this->fetchOne(
            'SELECT password FROM users WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        if (!$row) {
            return false;
        }
        return password_verify($plainPassword, $row['password']);
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        return $this->execute(
            'UPDATE users SET password = :password WHERE id = :id',
            [
                'password' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
                'id'       => $id,
            ]
        );
    }

    public static function validatePassword(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Le mot de passe doit contenir au moins une lettre majuscule.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Le mot de passe doit contenir au moins un chiffre.';
        }
        return null;
    }
}
