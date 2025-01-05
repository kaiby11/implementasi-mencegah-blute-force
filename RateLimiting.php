<?php
session_start();

// Simpan percobaan login di sesi
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lock_time'] = 0;
}

$max_attempts = 5;
$lockout_time = 900; // 15 menit

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (time() < $_SESSION['lock_time']) {
        die("Terlalu banyak percobaan login. Silakan coba lagi nanti.");
    }

    $stmt = $db->conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_attempts'] = 0; // Reset percobaan
        echo "Login berhasil!";
    } else {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= $max_attempts) {
            $_SESSION['lock_time'] = time() + $lockout_time;
            die("Terlalu banyak percobaan login. Akun Anda dikunci selama 15 menit.");
        }
        echo "Username atau password salah!";
    }
}
