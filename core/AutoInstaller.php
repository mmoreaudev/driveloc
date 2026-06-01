<?php
declare(strict_types=1);

final class AutoInstaller
{
    /**
     * Tables minimales attendues pour considérer le schéma comme initialisé.
     * Si une seule manque, l'application est considérée non prête.
     */
    private const REQUIRED_TABLES = [
        'categories',
        'users',
        'vehicles',
        'vehicle_images',
        'reservations',
    ];

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

        if (self::missingTables($pdo) === []) {
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

        $remainingMissing = self::missingTables($pdo);
        if ($remainingMissing !== []) {
            throw new RuntimeException(
                'Initialisation incomplète. Tables manquantes: ' . implode(', ', $remainingMissing)
            );
        }
    }

    public static function hasMissingRequiredTables(): bool
    {
        $pdo = Database::getInstance();
        return self::missingTables($pdo) !== [];
    }

    private static function missingTables(PDO $pdo): array
    {
        $missing = [];

        foreach (self::REQUIRED_TABLES as $table) {
            // MySQL n'accepte pas les placeholders prepares dans "SHOW TABLES LIKE ...".
            // On quote explicitement la valeur pour conserver une requete sure.
            $sql = 'SHOW TABLES LIKE ' . $pdo->quote($table);
            $stmt = $pdo->query($sql);

            if ($stmt === false || $stmt->fetchColumn() === false) {
                $missing[] = $table;
            }
        }

        return $missing;
    }
}
