<?php
declare(strict_types=1);

final class AutoInstaller
{
    public static function isEnabled(): bool
    {
        $value = getenv('AUTO_INSTALL_DB');

        if ($value === false || $value === '') {
            return false;
        }

        $normalized = strtolower(trim($value));
        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    public static function ensureDatabaseInitialized(): void
    {
        $pdo = Database::getInstance();

        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt !== false && $stmt->fetchColumn()) {
            return;
        }

        $sqlFile = ROOT_PATH . '/database/driveloc.sql';
        if (!is_file($sqlFile)) {
            throw new RuntimeException('Fichier SQL introuvable pour l\'installation initiale.');
        }

        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new RuntimeException('Impossible de lire le script SQL initial.');
        }

        // Nettoie les commentaires et neutralise les commandes interdites en mutualisé.
        $clean = preg_replace('/^\s*--.*$/m', '', $sql) ?? '';
        $statements = explode(';', $clean);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }

            if (preg_match('/^(DROP\s+DATABASE|CREATE\s+DATABASE|USE\s+)/i', $statement)) {
                continue;
            }

            $pdo->exec($statement);
        }
    }
}
