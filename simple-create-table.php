<?php
$dsn = 'mysql:host=127.0.0.1;dbname=laravel';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $sql = "CREATE TABLE IF NOT EXISTS coupon_user (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        coupon_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        UNIQUE KEY unique_coupon_user (coupon_id, user_id),
        CONSTRAINT fk_coupon_user_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
        CONSTRAINT fk_coupon_user_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);

    // Verify table exists
    $checkSql = "SHOW TABLES LIKE 'coupon_user'";
    $result = $pdo->query($checkSql);
    $tableExists = $result->rowCount() > 0;

    $message = $tableExists ? 'SUCCESS: Table created/exists' : 'FAIL: Table not found after creation';
    file_put_contents('result.txt', $message);
    echo $message;

} catch (PDOException $e) {
    $error = "ERROR: " . $e->getMessage();
    file_put_contents('result.txt', $error);
    echo $error;
}
?>
