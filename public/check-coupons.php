<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=laravel;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->query("
        SELECT c.id, c.code, c.status, c.start_date, c.end_date, c.discount_type, c.discount_value, COUNT(cu.user_id) as assigned_users
        FROM coupons c
        LEFT JOIN coupon_user cu ON c.id = cu.coupon_id
        GROUP BY c.id
        ORDER BY c.id DESC
    ");
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<h2>Coupons in Database:</h2>';
    echo '<table border="1" cellpadding="10" style="width:100%">';
    echo '<tr><th>ID</th><th>Code</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Type</th><th>Value</th><th>Assigned Users</th></tr>';
    foreach ($coupons as $coupon) {
        echo '<tr>';
        echo '<td>' . $coupon['id'] . '</td>';
        echo '<td>' . $coupon['code'] . '</td>';
        echo '<td>' . ($coupon['status'] ? '✓ Active' : '✗ Inactive') . '</td>';
        echo '<td>' . $coupon['start_date'] . '</td>';
        echo '<td>' . $coupon['end_date'] . '</td>';
        echo '<td>' . $coupon['discount_type'] . '</td>';
        echo '<td>' . $coupon['discount_value'] . '</td>';
        echo '<td>' . $coupon['assigned_users'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<h2>Coupons assigned to customer (ID=4):</h2>';
    $stmt = $pdo->prepare("
        SELECT c.id, c.code, c.status, c.start_date, c.end_date
        FROM coupons c
        INNER JOIN coupon_user cu ON c.id = cu.coupon_id
        WHERE cu.user_id = 4
        ORDER BY c.id
    ");
    $stmt->execute();
    $assigned = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($assigned)) {
        echo '<p>No coupons assigned to customer (ID=4)</p>';
    } else {
        echo '<table border="1" cellpadding="10" style="width:100%">';
        echo '<tr><th>ID</th><th>Code</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>';
        foreach ($assigned as $coupon) {
            echo '<tr>';
            echo '<td>' . $coupon['id'] . '</td>';
            echo '<td>' . $coupon['code'] . '</td>';
            echo '<td>' . ($coupon['status'] ? '✓' : '✗') . '</td>';
            echo '<td>' . $coupon['start_date'] . '</td>';
            echo '<td>' . $coupon['end_date'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
} catch (Exception $e) {
    echo '<h1 style="color: red;">ERROR: ' . htmlspecialchars($e->getMessage()) . '</h1>';
}
?>
