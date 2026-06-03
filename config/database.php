<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $instance = null;

    private const CHARSET = 'utf8mb4';

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host   = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            $user   = getenv('DB_USER');
            $pass   = getenv('DB_PASS');
            $port   = getenv('DB_PORT');

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $host,
                $port,
                $dbname,
                self::CHARSET
            );

            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$instance;
    }
}
