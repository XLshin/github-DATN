<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=laravel;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->query("SELECT id, name, email, role FROM users LIMIT 20");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<h2>Users in Database:</h2>';
    echo '<table border="1" cellpadding="10">';
    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>';
    foreach ($users as $user) {
        echo '<tr>';
        echo '<td>' . $user['id'] . '</td>';
        echo '<td>' . $user['name'] . '</td>';
        echo '<td>' . $user['email'] . '</td>';
        echo '<td><strong>' . $user['role'] . '</strong></td>';
        echo '</tr>';
    }
    echo '</table>';

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<p>Users with role="user": ' . $result['total'] . '</p>';
} catch (Exception $e) {
    echo '<h1 style="color: red;">ERROR: ' . htmlspecialchars($e->getMessage()) . '</h1>';
}
?>
