<?php
// ========================================
// DATABASE CLASS - PDO Singleton
// ========================================
class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                // Connect directly to the database with enforced UTF-8 (InfinityFree compatible)
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);

                // Force timezone and charset after connection just to be absolutely safe
                $pdo->exec("SET time_zone = '+07:00'");
                $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

                self::$instance = $pdo;

                // Run Code-First Migrator automatically ONLY if on Localhost
                $isLocal = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');
                if ($isLocal) {
                    require_once __DIR__ . '/Migrator.php';
                    $migrator = new Migrator(self::$instance);
                    $migrator->run();
                }

            } catch (PDOException $e) {
                if (defined('APP_DEBUG') && APP_DEBUG) {
                    die('Database connection failed: ' . $e->getMessage());
                } else {
                    $isRailway = isset($_ENV['MYSQLHOST']) || isset($_SERVER['MYSQLHOST']) || isset($_ENV['RAILWAY_SERVICE_NAME']);
                    if (!$isRailway && (isset($_ENV['RAILWAY_STATIC_URL']) || isset($_SERVER['RAILWAY_STATIC_URL']) || isset($_ENV['RAILWAY_SERVICE_NAME']))) {
                        die('Lỗi: Chưa tìm thấy dịch vụ MySQL trong dự án Railway của bạn. Hãy nhấn nút "+ Create" -> Database -> MySQL ở góc phải màn hình Railway!');
                    }
                    die('Service temporarily unavailable (Database Auto-Init Failed).');
                }
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchOne(string $sql, array $params = []): ?array {
        return self::query($sql, $params)->fetch() ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): int {
        $columns = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        self::query($sql, array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $sets = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$sets} WHERE {$where}";
        $stmt = self::query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public static function lastInsertId(): string {
        return self::getInstance()->lastInsertId();
    }

    public static function beginTransaction(): void {
        self::getInstance()->beginTransaction();
    }

    public static function commit(): void {
        self::getInstance()->commit();
    }

    public static function rollback(): void {
        self::getInstance()->rollBack();
    }
}
