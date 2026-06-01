<?php
declare(strict_types=1);

/**
 * Model – Classe de base pour tous les modèles.
 * Centralise l'accès PDO et les méthodes de requête réutilisables.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Prépare et exécute une requête SQL avec des paramètres liés.
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Retourne toutes les lignes d'un SELECT.
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Retourne une seule ligne, ou false si introuvable.
     */
    protected function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Exécute un INSERT/UPDATE/DELETE. Retourne true si au moins 1 ligne affectée.
     */
    protected function execute(string $sql, array $params = []): bool
    {
        return $this->query($sql, $params)->rowCount() > 0;
    }

    /**
     * Retourne le dernier ID auto-incrémenté.
     */
    protected function lastId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
