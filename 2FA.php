<?php
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 300; // Berlaku 5 menit

mail($email, "Kode OTP Anda", "Kode OTP Anda adalah: $otp");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $userOtp = $_POST['otp'];

    if (time() > $_SESSION['otp_expiry']) {
        die("Kode OTP telah kedaluwarsa.");
    }

    if ($userOtp == $_SESSION['otp']) {
        echo "Autentikasi berhasil!";
        unset($_SESSION['otp'], $_SESSION['otp_expiry']); // Hapus OTP
    } else {
        echo "Kode OTP salah!";
    }
}
