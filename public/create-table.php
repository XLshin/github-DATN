<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=laravel;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS `coupon_user` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
        `coupon_id` bigint unsigned NOT NULL,
        `user_id` bigint unsigned NOT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_coupon_user` (`coupon_id`, `user_id`),
        CONSTRAINT `fk_coupon_user_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_coupon_user_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    SQL;

    $pdo->exec($sql);

    // Verify
    $stmt = $pdo->query("SHOW TABLES LIKE 'coupon_user'");
    $exists = $stmt->rowCount() > 0;

    if ($exists) {
        echo '<h1 style="color: green;">✓ SUCCESS: coupon_user table created/exists!</h1>';
        echo '<p>The table is ready. You can now use the assign users feature.</p>';
    } else {
        echo '<h1 style="color: orange;">⚠ Table creation appeared successful but verification failed</h1>';
    }
} catch (PDOException $e) {
    echo '<h1 style="color: red;">✗ ERROR: ' . htmlspecialchars($e->getMessage()) . '</h1>';
} catch (Exception $e) {
    echo '<h1 style="color: red;">✗ ERROR: ' . htmlspecialchars($e->getMessage()) . '</h1>';
}
?>
