<?php
$ip_address = $_SERVER['REMOTE_ADDR'];
$max_attempts = 5;
$lockout_time = 900; // 15 menit

$stmt = $db->conn->prepare("SELECT * FROM login_attempts WHERE ip_address = ?");
$stmt->execute([$ip_address]);
$attempt = $stmt->fetch();

if ($attempt && $attempt['attempts'] >= $max_attempts && time() < $attempt['lock_time']) {
    die("IP Anda diblokir sementara. Silakan coba lagi nanti.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $db->conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip_address]); // Reset percobaan
        echo "Login berhasil!";
    } else {
        if ($attempt) {
            $stmt = $db->conn->prepare("UPDATE login_attempts SET attempts = attempts + 1, lock_time = ? WHERE ip_address = ?");
            $stmt->execute([time() + $lockout_time, $ip_address]);
        } else {
            $stmt = $db->conn->prepare("INSERT INTO login_attempts (ip_address, attempts, lock_time) VALUES (?, 1, ?)");
            $stmt->execute([$ip_address, time() + $lockout_time]);
        }
        echo "Username atau password salah!";
    }
}
?>